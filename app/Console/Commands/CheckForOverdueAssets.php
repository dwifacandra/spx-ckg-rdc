<?php

namespace App\Console\Commands;

use App\Models\AssetTransaction;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckForOverdueAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for assets currently "in use" that have exceeded the 9-hour limit and marks them as "overtime".';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Tentukan batas waktu: 9 jam yang lalu
        $overdueThreshold = Carbon::now()->subHours(9);

        // Cari transaksi yang:
        // 1. Statusnya 'in use'
        // 2. Belum ada waktu pengembalian (check_in is NULL)
        // 3. Waktu pinjam (check_out) lebih lama atau sama dengan batas waktu 9 jam yang lalu

        $overdueTransactions = AssetTransaction::where('status', 'in use')
            ->whereNull('check_in')
            ->where('check_out', '<=', $overdueThreshold)
            ->get();

        $count = 0;

        foreach ($overdueTransactions as $transaction) {
            $transaction->status = 'overtime';
            $transaction->save();
            $count++;
        }

        $this->info("Successfully checked for overdue assets. Updated {$count} transactions to 'overtime'.");

        return self::SUCCESS;
    }
}
