<?php

use App\Models\User;
use Livewire\Volt\Volt;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Route;

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
        Volt::route('tracker', 'assets.tracker')->name('tracker');
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Volt::route('/', 'assets.transactions.index')->name('index');
            Volt::route('checkin', 'assets.transactions.check-in')->name('checkin');
            Volt::route('checkout', 'assets.transactions.check-out')->name('checkout');
        });
    });

    Route::prefix('security')->name('security.')->group(function () {
        Volt::route('access_card', 'access_card.index')->name('access_card.index');
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

    // Generate Personal Access Token for Google Sheets Importer
    // Route::get('/generate-pat-once', function () {
    //     $apiUser = User::where('email', 'adityadwifacandra.adn@gmail.com')->first();

    //     if (!$apiUser) {
    //         return "User tidak ditemukan.";
    //     }

    //     $token = $apiUser->createToken('google-sheets-importer', ['read'])->plainTextToken;

    //     return $token;
    // });
});
