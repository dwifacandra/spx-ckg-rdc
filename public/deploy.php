<?php
// --- FILE INI HARUS SEGERA DIHAPUS SETELAH DIGUNAKAN! ---

// 1. MEMUAT FILE .ENV SECARA MANUAL (KOREKSI PENTING!)
// Kita berada di public/, jadi kita naik satu level ke direktori root proyek (..)
$dotenvPath = __DIR__ . '/../.env';
if (!file_exists($dotenvPath)) {
    http_response_code(500);
    die('Error: File .env tidak ditemukan di direktori root.');
}

// Menggunakan library vlucas/phpdotenv untuk membaca file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// 2. Validasi Keamanan (Wajib)
// Ambil kunci dari variabel lingkungan yang baru dimuat
$secretKey = env('DEPLOYMENT_SECRET'); // Menggunakan helper Laravel 'env()'
$inputKey = $_GET['key'] ?? null;

if (empty($secretKey) || $inputKey !== $secretKey) {
    http_response_code(403);
    die('Akses Ditolak: Kunci Rahasia Salah atau Hilang. Pastikan DEPLOYMENT_SECRET di .env sudah diatur dan kunci di URL sudah benar.');
}

// 3. Memuat Environment Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ... Sisa perintah maintenance (SAMA SEPERTI SEBELUMNYA) ...
header("Content-Type: text/plain");
echo "--- Memulai Deployment Maintenance ---\n\n";

echo "1. Menghapus Cache Lama (optimize:clear)...\n";
$kernel->call('optimize:clear');
echo "   Selesai.\n";

echo "2. Menjalankan Migrasi Database...\n";
$kernel->call('migrate', ['--force' => true]);
echo "   Selesai.\n";

echo "3. Mengoptimalkan Aplikasi (optimize)...\n";
$kernel->call('optimize');
echo "   Selesai.\n";

echo "4. Membersihkan Log Job Gagal (queue:flush)...\n";
$kernel->call('queue:flush');
echo "   Selesai.\n";

echo "\n--- Deployment Maintenance SELESAI! ---";
echo "\nSegera HAPUS file 'public/deploy.php' ini untuk keamanan.";
