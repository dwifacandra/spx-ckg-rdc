<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::redirect('/', '/dashboard')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');


    Route::prefix('assets')->name('assets.')->group(function () {
        Volt::route('/', 'assets.index')->name('index');
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Volt::route('/', 'assets.transactions.index')->name('index');
            Volt::route('checkin', 'assets.transactions.check-in')->name('checkin');
            Volt::route('checkout', 'assets.transactions.check-out')->name('checkout');
        });
    });

    Route::prefix('employees')->name('employees.')->group(function () {
        Volt::route('/', 'employees.index')->name('index');
    });

    // Route for downloading failed imports report
    Route::get('/download-failed-imports/{filename}', function ($filename) {
        $path = storage_path("app/temp/{$filename}");

        if (file_exists($path)) {
            return response()->download($path)->deleteFileAfterSend(true);
        }

        abort(404);
    })->name('download.failed.imports');
});
