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

class ImportEmployees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private $chunkData;
    private $filePath;
    private $delimiter;
    private $disk;
    private int $processedRows  = 0;
    private int $failedRows     = 0;
    private array $errorData    = [];
    protected $header           = [
        'ops_id',
        'staff_name',
        'gender',
        'passport_id',
        'employee_id',
        'blocklist',
        'contract_type',
        'joined_date',
        'last_date',
        'agency',
        'department',
        'station',
        'ops_status',
        'email',
        'soup_role',
    ];

    public function __construct(array $chunkData, string $delimiter)
    {
        $this->chunkData = $chunkData;
        $this->delimiter = $delimiter;
    }

    public function handle(): void
    {
        $this->batchId = $this->batch() ? $this->batch()->id : null;

        $now = now();
        $importedOpsIds = [];
        $batchData = [];
        $headerCount = count($this->header);

        // Fungsi helper untuk membersihkan data
        $cleanAndNullify = function ($value) {
            $cleaned = trim($value ?? '');
            return empty($cleaned) ? null : $cleaned;
        };

        // Loop melalui array baris data (sudah dibaca oleh Livewire)
        foreach ($this->chunkData as $line) {
            $this->processedRows++;

            // Parsing baris string ke array kolom menggunakan delimiter
            $row = str_getcsv($line, $this->delimiter);

            if ($this->batch() && $this->batch()->cancelled()) {
                break;
            }

            // 1. Pengecekan Integritas Baris
            if (count($row) < $headerCount) {
                // Catat kegagalan, menggunakan N/A karena baris tidak bisa diparsing
                $this->logError([], "Baris [Chunk] tidak memiliki cukup kolom (Ditemukan: " . count($row) . ").", 'INCOMPLETE_ROW');
                $this->failedRows++;
                continue;
            }

            // 2. Mapping Data
            $rowData = [
                'ops_id'        => $cleanAndNullify($row[0]),
                'staff_name'    => $cleanAndNullify($row[1]),
                'gender'        => $cleanAndNullify($row[2]),
                'passport_id'   => $cleanAndNullify($row[3]),
                'employee_id'   => $cleanAndNullify($row[4]),
                'blocklist'     => (bool)($cleanAndNullify($row[5]) ?? 0),
                'contract_type' => $cleanAndNullify($row[6]),
                'joined_date'   => $cleanAndNullify($row[7]),
                'last_date'     => $cleanAndNullify($row[8]),
                'agency'        => $cleanAndNullify($row[9]),
                'department'    => $cleanAndNullify($row[10]),
                'station'       => $cleanAndNullify($row[11]),
                'ops_status'    => $cleanAndNullify($row[12]),
                'email'         => $cleanAndNullify($row[13]),
                'soup_role'     => $cleanAndNullify($row[14]),
            ];

            $opsId = $rowData['ops_id'];

            // 3. Validasi Data Internal (di dalam chunk)
            if (empty($opsId)) {
                $this->logError($rowData, "Kolom 'ops_id' kosong (Kolom Wajib).", 'MISSING_OPS_ID');
                $this->failedRows++;
                continue;
            }

            if (in_array($opsId, $importedOpsIds)) {
                $this->logError($rowData, "Ops ID {$opsId} adalah duplikat di dalam file impor ini.", 'FILE_DUPLICATE');
                $this->failedRows++;
                continue;
            }
            $importedOpsIds[] = $opsId;

            $rowData['created_at'] = $now;
            $rowData['updated_at'] = $now;
            $batchData[] = $rowData;
        } // Akhir Loop

        // 4. INSERT DATA KE DATABASE
        if (!empty($batchData)) {
            $this->insertChunk($batchData);
        }

        // Buat failed report hanya untuk error yang terjadi di chunk ini
        if ($this->failedRows > 0 && $this->batchId) {
            // Beri nama unik agar tidak bentrok dengan Job lain
            $generatedFailedFileName = 'failed_import_' . $this->batchId . '_' . time() . '_' . uniqid() . '.csv';
            $this->createFailedReport($generatedFailedFileName);
        }

        if ($this->batchId) {
            $successfulCount = count($batchData) - ($this->failedRows - count($this->errorData));

            // Simpan hasil LOKAL Job ini. Gunakan properti batch ID dan Job ID yang unik
            Cache::put("job_stats_{$this->batchId}_{$this->job->getJobId()}", [
                'successful' => $successfulCount,
                'failed' => $this->failedRows,
                'processed' => $this->processedRows,
                'failed_file' => $generatedFailedFileName ?? null,
            ], now()->addHours(1));
        }
    }

    protected function insertChunk(array $chunkData): void
    {
        try {
            DB::table('employees')->insert($chunkData);
        } catch (Throwable $e) {
            // Jika batch insert gagal, coba masukkan satu per satu untuk mengidentifikasi baris yang bermasalah
            foreach ($chunkData as $rowData) {
                try {
                    // Masukkan baris tunggal
                    DB::table('employees')->insert($rowData);
                } catch (Throwable $singleInsertException) {
                    $this->failedRows++;
                    $errorMessage = $singleInsertException->getMessage();
                    $errorType = $this->getErrorType($errorMessage);

                    // Log error database dan catat ke errorData
                    $this->logError($rowData, $errorMessage, $errorType, true);
                }
            }
        }
    }

    protected function createFailedReport(string $fileName): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        $handle = fopen($tempFile, 'w');

        $headers = array_keys($this->errorData[0] ?? ['Ops ID', 'Staff Name', 'Reason', 'ErrorType']);
        fputcsv($handle, $headers);

        foreach ($this->errorData as $row) {
            $outputRow = [];
            foreach ($headers as $header) {
                $outputRow[] = $row[$header] ?? '';
            }
            fputcsv($handle, $outputRow);
        }

        fclose($handle);
        $contents = file_get_contents($tempFile);

        $filePath = "temp/{$fileName}";

        try {
            Storage::disk('local')->put($filePath, $contents);
            unlink($tempFile);
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan failed report ke disk.', [
                'error' => $e->getMessage(),
                'file' => $fileName
            ]);
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

    protected function getFriendlyErrorMessage(string $sqlError): string
    {
        if (str_contains($sqlError, 'Duplicate entry')) {
            if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $sqlError, $matches)) {
                $duplicateValue = $matches[1];
                return "Ops ID '$duplicateValue' sudah ada di database (Periksa Key: " . $matches[2] . ").";
            }
            return "Terjadi duplikasi data yang melanggar batasan unik.";
        }
        if (str_contains($sqlError, 'Data truncated for column')) {
            if (preg_match("/Data truncated for column '([^']+)'/", $sqlError, $matches)) {
                $columnName = $matches[1];
                return "Nilai terlalu panjang atau tidak valid untuk kolom '$columnName'. (Periksa tipe ENUM/panjang string).";
            }
            return "Nilai data terlalu panjang atau tidak sesuai tipe (ENUM/panjang).";
        }

        if (str_contains($sqlError, 'cannot be null')) {
            return "Terdapat kolom wajib (NOT NULL) yang dibiarkan kosong.";
        }

        return "Terjadi kesalahan database tak terduga: " . substr($sqlError, 0, 100) . "...";
    }

    protected function logError(array $rowData, string $errorReason, string $errorType, bool $isDbError = false): void
    {
        if ($isDbError) {
            $friendlyReason = $this->getFriendlyErrorMessage($errorReason);
        } else {
            $friendlyReason = $errorReason;
        }

        $logData = array_merge($rowData, [
            'error_reason' => $friendlyReason,
            'error_type' => $errorType,
        ]);

        Log::error('Employee Import Failed', $logData);

        $this->errorData[] = [
            'Ops ID' => $rowData['ops_id'] ?? 'N/A',
            'Staff Name' => $rowData['staff_name'] ?? 'N/A',
            'Reason' => $friendlyReason,
            'ErrorType' => $errorType,
        ];
    }
}
