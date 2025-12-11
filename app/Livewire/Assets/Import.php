<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Jobs\ImportAssets;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        $this->isImporting = true;
        $this->progress = 0;

        $filePath = $this->file->storeAs('temp_uploads', 'import_' . uniqid() . '.csv');
        $delimiter = $this->detectDelimiter($filePath);
        $batch = Bus::batch([
            new ImportAssets($filePath, $delimiter),
        ])->name('Import Employees ' . now()->format('YmdHis'))
            ->dispatch();

        $this->batchId = $batch->id;

        session()->flash('import_status', 'Import Assets dimulai di background. Silakan tunggu...');
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

            $this->isImporting      = false;
            $this->successfulCount  = $stats['successful'] ?? 0;
            $this->failedCount      = $stats['failed'] ?? 0;
            $total                  = $stats['processed'] ?? 0;
            $this->failedFileName   = $stats['failed_file_name'] ?? null;
            Cache::forget("import_stats_{$this->batchId}");
            $message                = "Import Selesai! (Berhasil: {$this->successfulCount}, Gagal: {$this->failedCount}, Total: {$total})";

            session()->flash('import_status', $message);
            $this->dispatch('refresh');
        }
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
        return view('livewire.assets.import');
    }
}
