<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Jobs\ImportEmployees;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batch;

class Import extends Component
{
    use WithFileUploads;

    public bool $show = false;
    public $file;
    public float $progress = 0;
    public bool $isImporting = false;
    public ?string $batchId = null;
    public int $successfulCount = 0;
    public int $failedCount = 0;
    public ?string $failedFileName = null;
    protected $listeners = ['show' => 'show'];

    public function getProgressWidthProperty(): string
    {
        return round($this->progress) . '%';
    }

    public function checkProgress()
    {
        if (!$this->isImporting || !$this->batchId) {
            return;
        }

        $batch = Bus::findBatch($this->batchId);

        if (!$batch) {
            $this->isImporting = false;
            session()->flash('import_status', 'Kesalahan: Status import tidak dapat dilacak.');
            $this->dispatch('refresh');
            return;
        }

        $this->progress = $batch->progress();

        if ($batch->finished()) {
            $stats = Cache::get("import_stats_{$this->batchId}");

            if (!$stats) {
                return;
            }

            $this->isImporting = false;

            $this->successfulCount = $stats['successful'] ?? 0;
            $this->failedCount = $stats['failed'] ?? 0;
            $total = $stats['processed'] ?? 0;
            $this->failedFileName = $stats['failed_file_name'] ?? null;

            Cache::forget("import_stats_{$this->batchId}");

            $message = "Import Selesai! (Berhasil: {$this->successfulCount}, Gagal: {$this->failedCount}, Total: {$total})";

            session()->flash('import_status', $message);
            $this->dispatch('refresh');
        }
    }

    public function show(): void
    {
        $this->show = true;
        $this->reset(['file', 'progress', 'isImporting', 'batchId', 'failedCount', 'failedFileName', 'successfulCount']);
    }

    protected function detectDelimiter(string $filePath): string
    {
        $possibleDelimiters = [';', ',', "\t", '|'];
        $handle = fopen(Storage::path($filePath), 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $bestDelimiter = ';';
        $maxCount = 0;

        foreach ($possibleDelimiters as $delimiter) {

            $count = substr_count($firstLine, $delimiter);

            if ($count > $maxCount && $count >= 5) {
                $maxCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        if ($maxCount === 0) {
            return ';';
        }

        return $bestDelimiter;
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $this->isImporting  = true;
        $this->progress     = 0;
        $filePath           = $this->file->storeAs('temp_uploads', 'import_' . uniqid() . '.csv');
        $delimiter          = $this->detectDelimiter($filePath);
        $allLines           = file(Storage::path($filePath), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($allLines)) {
            session()->flash('import_status', 'Kesalahan: File kosong.');
            $this->isImporting = false;
            return;
        }

        $dataLines          = array_slice($allLines, 1);
        $chunkSize          = 100;
        $chunks             = array_chunk($dataLines, $chunkSize);
        $jobs               = [];

        foreach ($chunks as $chunk) {
            $jobs[] = new ImportEmployees($chunk, $delimiter);
        }

        $batch = Bus::batch($jobs)
            ->name('Import Employees ' . now()->format('YmdHis'))

            // Callback ini dijalankan oleh Queue Worker setelah SEMUA jobs selesai (atau gagal)
            ->then(function (\Illuminate\Bus\Batch $batch) {
                \Illuminate\Support\Facades\Log::info('MASUK KE THEN CALLBACK (FINAL TRY)!', ['batch_id' => $batch->id]);

                $successfulSum = 0;
                $failedSum = 0;
                $processedSum = 0;
                $lastFailedFile = null;

                // ⚡️ PERBAIKAN: Gunakan $batch->jobs dan lakukan pengecekan ketat (Casting ke array)
                // Jika $batch->jobs null, kita akan menggunakan array kosong untuk menghindari crash
                $batchJobs = $batch->jobs;

                if (is_null($batchJobs)) {
                    \Illuminate\Support\Facades\Log::warning('Batch jobs property is NULL. Cannot compile detailed stats from batch object.');
                }

                // Pastikan properti Jobs adalah sesuatu yang bisa di-loop, jika null gunakan array kosong
                $jobsToProcess = is_countable($batchJobs) && !is_null($batchJobs) ? $batchJobs : [];

                foreach ($jobsToProcess as $job) {
                    // Karena $job di sini adalah Model Job (dari tabel batch),
                    // kita berasumsi ID-nya adalah ID yang benar yang digunakan Job Class.
                    // ID ini HARUS sama dengan hasil dari $this->job->getJobId() di Job Class Anda.

                    $jobId = $job->id; // Menggunakan ID Job dari tabel Batch

                    // Key yang digunakan di ImportEmployees.php: job_stats_{batch_id}_{Job ID unik}
                    $key = "job_stats_{$batch->id}_{$jobId}";
                    $stats = \Illuminate\Support\Facades\Cache::get($key);

                    if ($stats) {
                        $successfulSum += $stats['successful'];
                        $failedSum += $stats['failed'];
                        $processedSum += $stats['processed'];

                        if ($stats['failed_file']) {
                            $lastFailedFile = $stats['failed_file'];
                        }

                        \Illuminate\Support\Facades\Cache::forget($key);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Stats CACHE KEY TIDAK DITEMUKAN untuk Job.', ['key_expected' => $key]);
                    }
                }

                // Tulis statistik FINAL
                $finalStats = [
                    'successful' => $successfulSum,
                    'failed' => $failedSum,
                    'processed' => $processedSum,
                    'failed_file_name' => $lastFailedFile,
                ];

                \Illuminate\Support\Facades\Cache::put("import_stats_{$batch->id}", $finalStats, now()->addMinutes(30));
                \Illuminate\Support\Facades\Log::info('FINAL STATS BERHASIL DITULIS!', ['final_stats' => $finalStats]);
            })

            // Callback untuk cleanup file sumber
            ->finally(function () use ($filePath) {
                Storage::delete($filePath);
            })
            ->dispatch();
        $this->batchId      = $batch->id;

        session()->flash('import_status', 'Import Karyawan dimulai di background. Silakan tunggu...');
    }

    public function downloadFailedReport()
    {
        if (!$this->failedFileName) {
            abort(404, 'No failed report available.');
        }

        $filePath = "temp/{$this->failedFileName}";

        if (!Storage::disk('local')->exists($filePath)) {
            Log::error("Failed report file not found: {$filePath}");
            abort(404, 'Report file not found on server.');
        }

        return Storage::download($filePath, $this->failedFileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        return view('livewire.employees.import');
    }
}
