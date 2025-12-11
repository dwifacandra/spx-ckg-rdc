<?php

namespace App\Livewire\Assets\Transactions;

use App\Models\Asset;
use Livewire\Component;
use App\Models\AssetTransaction;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class CheckOut extends Component
{
    public string $opsId = '';
    public string $assetCode = '';
    public ?Asset $selectedAsset = null;
    public ?Employee $selectedEmployee = null;
    public string $statusMessage = 'Waiting for scan';
    public string $failureReason = '';

    public function updatedAssetCode($value)
    {
        // Clean and capitalize the input
        $this->assetCode = $this->cleanAndCapitalize($value);

        if ($this->assetCode) {
            $this->selectedAsset = Asset::where('code', $this->assetCode)->first();
            $this->checkAutoSubmit();
        } else {
            $this->selectedAsset = null;
        }
    }

    public function updatedOpsId($value)
    {
        $this->opsId = $this->cleanAndCapitalize($value);

        if ($this->opsId) {
            $pattern = '/^OPS[0-9]+$/';
            $employee = Employee::where('ops_id', $this->opsId)->first();

            if ($employee) {
                $this->selectedEmployee = $employee;
            } elseif (preg_match($pattern, $this->opsId)) {
                $this->selectedEmployee = Employee::create([
                    'ops_id' => $this->opsId,
                    'staff_name' => '[NOT RECORDED]',
                    'ops_status' => 'temperory',
                ]);
            } else {
                $this->selectedEmployee = null;
            }
            $this->checkAutoSubmit();
        } else {
            $this->selectedEmployee = null;
        }
    }

    public function checkAutoSubmit()
    {
        if (!empty($this->opsId) && !empty($this->assetCode)) {
            $this->save();
        }
    }

    public function save()
    {
        // Validate only if we have both fields
        if (empty($this->opsId) || empty($this->assetCode)) {
            return;
        }

        if (!$this->selectedAsset) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Asset tidak ditemukan';
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
            return;
        }

        // 1. Cek Status Menggunakan Metode isCheckedOut()
        if ($this->selectedAsset->isCheckedOut()) {
            // Kasus A: Asset sedang aktif dipinjam (Checked Out)
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Asset sedang digunakan';
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
            return;
        }

        // 2. Jika Lolos Pengecekan (Siap Dipinjam)
        try {
            DB::transaction(function () {
                // A. Buat Transaksi Peminjaman (Check Out)
                AssetTransaction::create([
                    'asset_id' => $this->cleanAndCapitalize($this->selectedAsset->code),
                    'ops_id' => $this->cleanAndCapitalize($this->selectedEmployee->ops_id),
                    'check_out' => now(),
                    'status' => 'in use', // Status transaksi: Sedang dipinjam
                    'created_by' => auth()->id(),
                ]);

                // B. Perbarui Status Fisik Aset di Tabel Asset (Sinkronisasi)
                $this->selectedAsset->status = 'in use';
                $this->selectedAsset->save();

                $this->statusMessage = 'Check Out';
            });

            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
        } catch (\Exception $e) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Gagal menyimpan transaksi: ' . $e->getMessage();
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
        }
    }

    private function cleanAndCapitalize(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // Trim whitespace and capitalize
        return strtoupper(trim($value));
    }

    public function render()
    {
        $recentTransactions = AssetTransaction::with('asset')
            ->where('status', 'in use')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.assets.transactions.check-out', [
            'recentTransactions' => $recentTransactions
        ]);
    }
}
