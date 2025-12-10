<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Menjalankan command setiap 15 menit
// cron job * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('assets:check-overdue')->everyFiveMinutes();
