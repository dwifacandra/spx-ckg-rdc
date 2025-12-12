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
    public string $checkInDateFilter = '';
    public string $checkOutDateFilter = '';
    public string $sortField = 'check_out';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'checkInDateFilter' => ['except' => ''],
        'checkOutDateFilter' => ['except' => ''],
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

    public function updatingCheckInDateFilter()
    {
        $this->resetPage();
    }

    public function updatingCheckOutDateFilter()
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
        $this->reset(['search', 'statusFilter', 'checkInDateFilter', 'checkOutDateFilter']);
        $this->sortField = 'check_out';
        $this->sortDirection = 'desc';
    }

    public function getAssetStats(): array
    {
        $STATUS_IN_USE      = 'in use';
        $STATUS_COMPLETE    = 'complete';
        $STATUS_OVERTIME    = 'overtime';
        $TYPE_PDA           = 'PDA';
        $TYPE_HT            = 'HT';
        $stats              = [
            'active'    => [],
            'complete'  => [],
            'overdue'   => [],
        ];

        $calculateDetails = function (string $status) use ($TYPE_PDA, $TYPE_HT): array {
            $pda_count = Asset::where('item', $TYPE_PDA)
                ->whereHas('lastTransaction', fn($q) => $q->where('status', $status))
                ->count();

            $ht_count = Asset::where('item', $TYPE_HT)
                ->whereHas('lastTransaction', fn($q) => $q->where('status', $status))
                ->count();

            return [
                'total' => $pda_count + $ht_count,
                'pda' => $pda_count,
                'ht' => $ht_count,
            ];
        };
        $stats['active']    = $calculateDetails($STATUS_IN_USE);
        $stats['complete']  = $calculateDetails($STATUS_COMPLETE);
        $stats['overdue']   = $calculateDetails($STATUS_OVERTIME);
        $stats['total']     = [
            'total'     => Asset::count(),
            'pda'       => Asset::where('item', $TYPE_PDA)->count(),
            'ht'        => Asset::where('item', $TYPE_HT)->count(),
        ];

        return $stats;
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
                            ->orWhere('tag', 'like', '%' . $this->search . '%')
                            ->orWhere('serial_number', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('ops_id', 'like', '%' . $this->search . '%')
                        ->orWhere('created_by', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->checkInDateFilter, function ($query) {
                $query->whereDate('check_in', '>=', $this->checkInDateFilter);
            })
            ->when($this->checkOutDateFilter, function ($query) {
                $query->whereDate('check_out', '<=', $this->checkOutDateFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.assets.transactions.index', [
            'transactions' => $transactions,
            'stats' => $this->getAssetStats()
        ]);
    }
}
