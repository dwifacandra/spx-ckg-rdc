<?php

namespace App\Livewire\Assets\Transactions;

use App\Models\Asset;
use Livewire\Component;
use App\Models\Employee;
use App\Models\AssetTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckIn extends Component
{
    public string $opsId = '';
    public string $assetCode = '';
    public ?Asset $selectedAsset = null;
    public ?Employee $selectedEmployee = null;
    public string $statusMessage = 'Waiting for scan';
    public string $failureReason = '';
    public string $remark = '';

    // --- Hooks untuk Input (onblur) ---

    public function updatedAssetCode($value)
    {
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

    // --- Logika Autosubmit ---

    public function checkAutoSubmit()
    {
        if (!empty($this->opsId) && !empty($this->assetCode)) {
            $this->save();
        }
    }

    // --- Logika Force Check In ---

    public function forceCheckIn()
    {
        if (!$this->selectedAsset) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Tidak ada aset terdeteksi untuk Force Check In.';
            return;
        }

        $activeTransaction = $this->findActiveTransaction(false);

        if (!$activeTransaction) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Asset tidak sedang dipinjam.';
            return;
        }

        $remarkText = "FORCED CHECK IN: ";

        if ($this->statusMessage === 'OPS ID Mismatch') {
            $remarkText .= "OPS ID mismatch detected. Expected: " . $activeTransaction->ops_id . ". Scanned: " . $this->opsId;
        } elseif (!empty($this->failureReason)) {
            $remarkText .= $this->failureReason;
        } else {
            $remarkText .= "Manual override by user: " . (Auth::check() ? Auth::user()->name : 'System User');
        }

        $this->executeCheckIn($activeTransaction, $remarkText);
    }

    // --- Logika Utama Check In (Pengembalian Normal) ---

    public function save()
    {
        if (empty($this->assetCode) || empty($this->opsId)) {
            if (empty($this->assetCode)) {
                $this->statusMessage = 'Failed';
                $this->failureReason = 'Asset Code wajib diisi.';
            } elseif (empty($this->opsId)) {
                $this->statusMessage = 'Failed';
                $this->failureReason = 'OPS ID wajib diisi.';
            }
            return;
        }

        if (!$this->selectedAsset) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Asset tidak ditemukan';
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
            return;
        }

        $activeTransaction = $this->findActiveTransaction(false);

        if (!$activeTransaction) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Asset tidak sedang dipinjam.';
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
            return;
        }

        // VALIDASI KETAT: OPS ID harus sesuai dengan transaksi aktif
        if ($activeTransaction->ops_id !== $this->opsId) {
            $this->statusMessage = 'OPS ID Mismatch';
            $this->failureReason = 'Asset [' . $this->assetCode . '] dipinjam oleh OPS ID: ' . $activeTransaction->ops_id . ' / ' . $activeTransaction->staff_name . '. Mohon scan atau masukkan OPS ID yang sesuai.';
            return;
        }

        $this->executeCheckIn($activeTransaction, null);
    }

    // --- Metode Pembantu ---

    private function findActiveTransaction(bool $useOpsIdFilter = true)
    {
        if (!$this->selectedAsset) return null;

        $activeTransactionQuery = $this->selectedAsset->transactions()
            ->whereNotNull('check_out')
            ->whereNull('check_in')
            ->where(function ($query) {
                // Status aktif yang memerlukan Check In
                $query->where('status', 'in use')
                    ->orWhere('status', 'overtime');
            })
            ->latest('created_at');

        return $activeTransactionQuery->first();
    }

    private function executeCheckIn(AssetTransaction $activeTransaction, ?string $remark = null)
    {
        try {
            DB::transaction(function () use ($activeTransaction, $remark) {
                // A. Update Transaksi Aktif (Tabel asset_transactions)
                $activeTransaction->check_in = now();
                $activeTransaction->status = 'complete';

                if ($remark !== null) {
                    $activeTransaction->remarks = $remark;
                }

                $activeTransaction->save();

                $this->statusMessage = 'Check In';
                $this->failureReason = '';
            });

            $this->reset(['opsId', 'assetCode', 'selectedAsset', 'selectedEmployee', 'remark']);
            $this->dispatch('focus-ops-id');
        } catch (\Exception $e) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Gagal menyimpan transaksi: ' . $e->getMessage();
            $this->reset(['opsId', 'assetCode', 'selectedAsset', 'selectedEmployee', 'remark']);
            $this->dispatch('focus-ops-id');
        }
    }

    private function cleanAndCapitalize(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        return strtoupper(trim($value));
    }

    public function render()
    {
        $recentTransactions = AssetTransaction::with('asset')
            ->where('status', 'complete')
            ->orderBy('check_out', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.assets.transactions.check-in', [
            'recentTransactions' => $recentTransactions
        ]);
    }
}
