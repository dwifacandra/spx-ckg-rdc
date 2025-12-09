<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ImportAssets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private $filePath;
    private $delimiter;
    private $disk;

    private int $processedRows = 0;
    private int $failedRows = 0;
    private array $errorData = [];
    // Properti $batchId dihapus karena sudah disediakan oleh trait Batchable.

    public function __construct(string $filePath, string $delimiter = ';', string $disk = 'local')
    {
        if (strlen($delimiter) !== 1) {
            $delimiter = ';';
        }

        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->disk = $disk;
    }

    public function handle(): void
    {
        // Mendapatkan ID batch saat ini
        $currentBatchId = $this->batch() ? $this->batch()->id : null;

        try {
            $fullPath = Storage::disk($this->disk)->path($this->filePath);
            $fileHandle = fopen($fullPath, 'r');
        } catch (Throwable $e) {
            $this->fail(new \Exception("Gagal membuka file: " . $this->filePath . " | Error: " . $e->getMessage()));
            return;
        }

        $now = now();
        $importedCodes = [];
        $batchData = [];
        $chunkSize = 1000;
        $lineCount = 0;

        $header = [
            'item',
            'brand',
            'code',
            'type',
            'tag',
            'serial_number',
            'condition',
            'status',
            'remarks',
            'ownership'
        ];

        // Lewati header
        fgetcsv($fileHandle, 0, $this->delimiter);

        $cleanAndNullify = function ($value) {
            $cleaned = trim($value ?? '');
            return empty($cleaned) ? null : $cleaned;
        };

        // --- Proses Pembacaan Data ---
        while (($row = fgetcsv($fileHandle, 0, $this->delimiter)) !== false) {
            $lineCount++;
            $this->processedRows++;

            if ($this->batch() && $this->batch()->cancelled()) {
                break;
            }

            if (count($row) < count($header)) {
                $this->logError([], "Baris {$lineCount} tidak memiliki cukup kolom.", 'INCOMPLETE_ROW');
                $this->failedRows++;
                continue;
            }

            $rowData = [
                'item'          => $cleanAndNullify($row[0]),
                'brand'         => $cleanAndNullify($row[1]),
                'code'          => $cleanAndNullify($row[2]),
                'type'          => $cleanAndNullify($row[3]),
                'tag'           => $cleanAndNullify($row[4]),
                'serial_number' => $cleanAndNullify($row[5]),
                'condition'     => $cleanAndNullify($row[6]),
                'status'        => $cleanAndNullify($row[7]),
                'remarks'       => $cleanAndNullify($row[8]),
                'ownership'     => $cleanAndNullify($row[9]),
            ];

            $code = $rowData['code'];

            if (empty($code)) {
                $this->logError($rowData, "Kolom 'code' kosong di baris {$lineCount}.", 'MISSING_CODE');
                $this->failedRows++;
                continue;
            }


            if (in_array($code, $importedCodes)) {
                $this->logError($rowData, "Kode {$code} adalah duplikat di dalam file impor ini.", 'FILE_DUPLICATE');
                $this->failedRows++;
                continue;
            }
            $importedCodes[] = $code;
            $rowData['created_at'] = $now;
            $rowData['updated_at'] = $now;
            $batchData[] = $rowData;

            if (count($batchData) >= $chunkSize) {
                $this->insertChunk($batchData, $lineCount);
                $batchData = [];
            }
        }

        if (!empty($batchData)) {
            $this->insertChunk($batchData, $lineCount);
        }

        fclose($fileHandle);

        // Hapus file yang diupload
        Storage::disk($this->disk)->delete($this->filePath);

        $generatedFailedFileName = null;
        if ($this->failedRows > 0 && $currentBatchId) {
            $generatedFailedFileName = 'failed_import_' . $currentBatchId . '_' . time() . '.csv';
            $this->createFailedReport($generatedFailedFileName);
        }

        // PENYIMPANAN STATISTIK DAN NAMA FILE GAGAL KE CACHE GLOBAL
        if ($currentBatchId) {
            $stats = [
                'processed'         => $this->processedRows,
                'successful'        => $this->processedRows - $this->failedRows,
                'failed'            => $this->failedRows,
                'failed_file_name'  => $generatedFailedFileName,
            ];

            Cache::put("import_stats_{$currentBatchId}", $stats, now()->addMinutes(30));
        }
    }

    // --- Metode Pembuatan Laporan Kegagalan (Memperbaiki ArgumentCountError) ---

    protected function createFailedReport(string $fileName): void
    {
        // 1. Buat file temporary di sistem untuk menulis CSV
        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        $handle = fopen($tempFile, 'w');

        $headers = array_keys($this->errorData[0] ?? ['Code', 'Item', 'Reason', 'ErrorType']);
        fputcsv($handle, $headers);

        // 2. Tulis data kegagalan ke temporary stream
        foreach ($this->errorData as $row) {
            $outputRow = [];
            foreach ($headers as $header) {
                $outputRow[] = $row[$header] ?? '';
            }
            fputcsv($handle, $outputRow);
        }

        // Tutup stream dan dapatkan konten mentah file
        fclose($handle);
        $contents = file_get_contents($tempFile);

        // 3. Simpan konten ke disk Laravel menggunakan Storage::put()
        $filePath = "temp/{$fileName}";

        try {
            // Menggunakan put() daripada writeStream($path)
            Storage::disk('local')->put($filePath, $contents);
            unlink($tempFile);
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan failed report ke disk.', [
                'error' => $e->getMessage(),
                'file' => $fileName
            ]);
        }
    }

    // --- Metode Pemrosesan Error Database ---

    protected function insertChunk(array $chunkData, int $currentLineCount): void
    {
        try {
            DB::table('assets')->insert($chunkData);
        } catch (Throwable $e) {
            // Jika chunk gagal, coba masukkan satu per satu untuk mengidentifikasi baris yang bermasalah
            foreach ($chunkData as $rowData) {
                try {
                    DB::table('assets')->insert($rowData);
                } catch (Throwable $singleInsertException) {
                    $this->failedRows++;
                    $errorMessage = $singleInsertException->getMessage();
                    $errorType = $this->getErrorType($errorMessage);
                    // Panggil logError dengan flag TRUE untuk memberitahu bahwa ini error DB
                    $this->logError($rowData, $errorMessage, $errorType, true);
                }
            }
        }
    }

    protected function getErrorType(string $message): string
    {
        if (str_contains($message, 'Data truncated for column')) {
            return 'DATA_TRUNCATED_ERROR (ENUM/Length)';
        }
        if (str_contains($message, 'Duplicate entry')) {
            return 'DUPLICATE_ENTRY_ERROR';
        }
        if (str_contains($message, 'cannot be null')) {
            return 'MISSING_REQUIRED_DATA';
        }
        return 'DATABASE_CONSTRAINT_ERROR';
    }

    // --- Metode Pembersihan Pesan Error (Friendly Reason) ---

    protected function getFriendlyErrorMessage(string $sqlError): string
    {
        // DUPLICATE ENTRY ERROR
        if (str_contains($sqlError, 'Duplicate entry')) {
            if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $sqlError, $matches)) {
                $duplicateValue = $matches[1];
                return "Kode aset '$duplicateValue' sudah ada di database (Periksa Key: " . $matches[2] . ").";
            }
            return "Terjadi duplikasi data yang melanggar batasan unik.";
        }

        // DATA TRUNCATED ERROR
        if (str_contains($sqlError, 'Data truncated for column')) {
            if (preg_match("/Data truncated for column '([^']+)'/", $sqlError, $matches)) {
                $columnName = $matches[1];
                return "Nilai terlalu panjang atau tidak valid untuk kolom '$columnName'. (Periksa tipe ENUM/panjang string).";
            }
            return "Nilai data terlalu panjang atau tidak sesuai tipe (ENUM/panjang).";
        }

        // MISSING REQUIRED DATA
        if (str_contains($sqlError, 'cannot be null')) {
            return "Terdapat kolom wajib (NOT NULL) yang dibiarkan kosong.";
        }

        // DEFAULT FALLBACK
        return "Terjadi kesalahan database tak terduga: " . substr($sqlError, 0, 100) . "...";
    }

    protected function logError(array $rowData, string $errorReason, string $errorType, bool $isDbError = false): void
    {
        // Bersihkan pesan error jika berasal dari SQL
        if ($isDbError) {
            $friendlyReason = $this->getFriendlyErrorMessage($errorReason);
        } else {
            $friendlyReason = $errorReason;
        }

        $logData = array_merge($rowData, [
            'error_reason' => $friendlyReason,
            'error_type' => $errorType,
        ]);

        Log::error('Asset Import Failed', $logData);

        // Simpan data kegagalan untuk laporan CSV
        $this->errorData[] = [
            'Code' => $rowData['code'] ?? 'N/A',
            'Item' => $rowData['item'] ?? 'N/A',
            'Reason' => $friendlyReason, // Menggunakan pesan yang ramah pengguna
            'ErrorType' => $errorType,
        ];
    }
}
