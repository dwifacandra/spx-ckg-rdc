<?php


namespace App\Livewire\Assets\Transactions;

use Livewire\Component;
use App\Models\AssetTransaction;
use App\Models\Asset;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sortField = 'check_out';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'check_in'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }


    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->sortField = 'check_out';
        $this->sortDirection = 'desc';
    }

    public function render()
    {
        $transactions = AssetTransaction::query()
            ->with('asset')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('asset', function ($assetQuery) {
                        $assetQuery->where('code', 'like', '%' . $this->search . '%')
                            ->orWhere('item', 'like', '%' . $this->search . '%')
                            ->orWhere('brand', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('ops_id', 'like', '%' . $this->search . '%')
                        ->orWhere('created_by', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $stats = [
            'total' => AssetTransaction::count(),
            'active' => AssetTransaction::where('status', 'in use')->whereNull('check_in')->count(),
            'complete' => AssetTransaction::where('status', 'complete')->count(),
            'overdue' => AssetTransaction::where('status', 'overtime')->whereNull('check_in')->count(),
        ];


        return view('livewire.assets.transactions.index', [
            'transactions' => $transactions,
            'stats' => $stats
        ]);
    }
}
