<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'deploy:now'; // Nama command yang mudah diingat

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Perintah maintenance setelah deployment: migrate, clear cache, dan optimize.';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $this->info('--- Memulai Deployment Maintenance ---');

        // 1. Membersihkan Cache Konfigurasi, Route, dan View LAMA
        $this->call('optimize:clear');
        $this->info('Cache lama dihapus.');

        // 2. Menjalankan Migrasi Database
        $this->call('migrate', ['--force' => true]);
        $this->info('Migrasi selesai.');

        // 3. Mengoptimalkan Aplikasi (Membuat cache konfigurasi dan route yang BARU)
        $this->call('optimize');
        $this->info('Optimasi selesai.');

        // 4. Membersihkan Log Job Gagal (Opsional, tapi direkomendasikan)
        $this->call('queue:flush');
        $this->info('Log job gagal dibersihkan.');

        $this->info('--- Deployment Selesai! ---');
        return 0;
    }
}
