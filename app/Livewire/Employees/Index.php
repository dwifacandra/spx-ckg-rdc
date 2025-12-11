<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
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
        $headers = [
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
        $filename = 'employee_import_template.csv';

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
            Employee::create([
                'ops_id'        => $row[0] ?? null,
                'staff_name'    => $row[1] ?? '',
                'gender'        => strtolower($row[2] ?? 'other'),
                'passport_id'   => $row[3] ?? null,
                'employee_id'   => $row[4] ?? null,
                'blocklist'     => ($row[5] ?? 0) == 1,
                'contract_type' => $row[6] ?? '',
                'joined_date'   => $row[7] ?? null,
                'last_date'     => $row[8] ?? null,
                'agency'        => $row[9] ?? '',
                'department'    => $row[10] ?? null,
                'station'       => $row[11] ?? null,
                'ops_status'    => strtolower($row[12] ?? 'active'),
                'email'         => $row[13] ?? null,
                'soup_role'     => $row[14] ?? null,
            ]);
        }

        session()->flash('message', 'Employees imported successfully.');
        $this->reset('file');
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function ($query) {
                $query->where('ops_id', 'like', '%' . $this->search . '%')
                    ->orWhere('staff_name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.employees.index', compact('employees'));
    }
}
