<?php
// --- FILE INI HARUS SEGERA DIHAPUS SETELAH DIGUNAKAN! ---

// 1. Validasi Keamanan (Wajib)
$secretKey = getenv('DEPLOYMENT_SECRET');
$inputKey = $_GET['key'] ?? null;

if (empty($secretKey) || $inputKey !== $secretKey) {
    http_response_code(403);
    die('Akses Ditolak: Kunci Rahasia Salah atau Hilang. Pastikan DEPLOYMENT_SECRET di .env sudah diatur dan kunci di URL sudah benar.');
}

// 2. Memuat Environment Laravel
// Kita berada di public/, jadi kita naik satu level ke vendor/
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mengirim header text/plain agar output mudah dibaca di browser
header("Content-Type: text/plain");
echo "--- Memulai Deployment Maintenance ---\n\n";

// --- 3. Perintah Maintenance ---

// a. CLEAR CACHE LAMA (Wajib sebelum optimize)
echo "1. Menghapus Cache Lama (optimize:clear)...\n";
$kernel->call('optimize:clear');
echo "   Selesai.\n";

// b. JALANKAN MIGRASI DATABASE (Wajib jika ada perubahan skema DB)
echo "2. Menjalankan Migrasi Database...\n";
// Menggunakan '--force' karena ini mode produksi
$kernel->call('migrate', ['--force' => true]);
echo "   Selesai.\n";

// c. OPTIMASI APLIKASI (Membuat ulang cache konfigurasi)
echo "3. Mengoptimalkan Aplikasi (optimize)...\n";
$kernel->call('optimize');
echo "   Selesai.\n";

// d. CLEANUP JOB GAGAL (Opsional, tapi baik untuk menjaga database tetap bersih)
echo "4. Membersihkan Log Job Gagal (queue:flush)...\n";
$kernel->call('queue:flush');
echo "   Selesai.\n";

echo "\n--- Deployment Maintenance SELESAI! ---";
echo "\nSegera HAPUS file 'public/deploy.php' ini untuk keamanan.";
