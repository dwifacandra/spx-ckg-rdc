<?php

use App\Http\Controllers\Api\TransactionHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/asset-transactions', [TransactionHistoryController::class, 'index'])
        ->name('api.transactions.history');
});
