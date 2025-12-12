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

    public function checkAutoSubmit()
    {
        if (!empty($this->opsId) && !empty($this->assetCode)) {
            $this->save();
        }
    }

    public function forceCheckIn()
    {
        if (empty($this->opsId)) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'OPS ID wajib diisi untuk Force Check In.';
            return;
        }

        $activeTransactions = AssetTransaction::query()
            ->where('ops_id', $this->opsId)
            ->whereNotNull('check_out')
            ->whereNull('check_in')
            ->where(fn($query) => $query->where('status', 'in use')->orWhere('status', 'overtime'))
            ->get();

        if ($activeTransactions->isEmpty()) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'OPS ID [' . $this->opsId . '] tidak memiliki pinjaman aktif untuk Force Check In.';
            return;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($activeTransactions as $activeTransaction) {
            $remarkText = "FORCED CHECK IN (ADMIN: " . (Auth::check() ? Auth::user()->name : 'System') . "): ";

            if (!empty($this->assetCode)) {
                $remarkText .= "Asset Code scanned for override: [" . $this->assetCode . "]. Transaction Asset: [" . $activeTransaction->asset_id . "].";
            } else {
                $remarkText .= "Manual batch override. ";
            }

            try {
                $this->executeCheckIn($activeTransaction, $remarkText);
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
            }
        }

        if ($successCount > 0) {
            $this->statusMessage = 'Force Check In Successful';
            $this->failureReason = "Berhasil mengakhiri {$successCount} pinjaman aktif untuk OPS ID [{$this->opsId}]."
                . ($failureCount > 0 ? " ({$failureCount} transaksi gagal diproses)." : "");
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
        } else {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Terjadi kesalahan saat memproses Force Check In.';
        }
    }

    public function save()
    {
        if (empty($this->assetCode) || empty($this->opsId)) {
            $this->statusMessage = 'Failed';
            $this->failureReason = empty($this->assetCode) ? 'Asset Code wajib diisi.' : 'OPS ID wajib diisi.';
            return;
        }

        if (!$this->selectedAsset) {
            $this->statusMessage = 'Failed';
            $this->failureReason = 'Asset tidak terdaftar.';
            $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            $this->dispatch('focus-ops-id');
            return;
        }

        $activeTransaction = $this->findActiveTransaction(true);

        if (!$activeTransaction) {
            $anyActiveTransaction = $this->findActiveTransaction(false);

            if ($anyActiveTransaction) {
                $findOpsIdScannedAssetCode = AssetTransaction::where('asset_id', $this->assetCode)->first();
                $this->statusMessage = 'Asset Mismatch';
                $this->failureReason = 'Expected: [' . $anyActiveTransaction->asset_id . '], Scanned: [' . $this->assetCode . ']<br/>'
                    . $this->assetCode . ' Checked Out by '
                    . strtoupper($findOpsIdScannedAssetCode->ops_id) . ' / '
                    . $findOpsIdScannedAssetCode->ops_profile->staff_name . '.';
            } else {
                $this->statusMessage = 'Failed';
                $this->failureReason = 'OPS ID [' . $this->opsId . '] tidak memiliki pinjaman aktif.';

                $this->reset(['opsId', 'assetCode', 'selectedEmployee', 'selectedAsset']);
            }
            $this->dispatch('focus-ops-id');
            return;
        }
        $this->executeCheckIn($activeTransaction, null);
    }

    private function findActiveTransaction(bool $useAssetCodeFilter = true)
    {
        if (empty($this->opsId)) {
            return null;
        }

        $activeTransactionQuery = AssetTransaction::query()
            ->where('ops_id', $this->opsId)
            ->whereNotNull('check_out')
            ->whereNull('check_in')
            ->where(function ($query) {
                $query->where('status', 'in use')
                    ->orWhere('status', 'overtime');
            });

        if ($useAssetCodeFilter && !empty($this->assetCode)) {
            $activeTransactionQuery->where('asset_id', $this->assetCode);
        }

        return $activeTransactionQuery->latest('created_at')->first();
    }

    private function executeCheckIn(AssetTransaction $activeTransaction, ?string $remark = null)
    {
        try {
            DB::transaction(function () use ($activeTransaction, $remark) {
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
            $this->dispatch('transaction-saved');
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
            ->orderBy('check_in', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.assets.transactions.check-in', [
            'recentTransactions' => $recentTransactions
        ]);
    }
}
