<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $file;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function downloadTemplate()
    {
        $headers = ['Item', 'Brand', 'Code', 'Type', 'Tag', 'Serial Number', 'Condition', 'Status', 'Remarks', 'Ownership'];
        $filename = 'asset_template.csv';

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function updatedFile()
    {
        $this->import();
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:csv,xlsx|max:10240', // 10MB max
        ]);

        $path = $this->file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        // Skip header row
        array_shift($data);

        foreach ($data as $row) {
            Asset::create([
                'item' => $row[0] ?? '',
                'brand' => $row[1] ?? '',
                'code' => $row[2] ?? '',
                'type' => $row[3] ?? '',
                'tag' => $row[4] ?? '',
                'serial_number' => $row[5] ?? '',
                'condition' => $row[6] ?? 'good',
                'status' => $row[7] ?? 'in use',
                'remarks' => $row[8] ?? '',
                'ownership' => $row[9] ?? '',
            ]);
        }

        session()->flash('message', 'Assets imported successfully.');
        $this->reset('file');
    }

    public function render()
    {
        $assets = Asset::query()
            ->when($this->search, function ($query) {
                $query->where('item', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('tag', 'like', '%' . $this->search . '%')
                    ->orWhere('serial_number', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.assets.index', compact('assets'));
    }
}
