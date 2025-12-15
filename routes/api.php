<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public health check (no auth required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Version 1 API routes
Route::prefix('v1')->group(function () {
    // Authentication endpoints
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::post('/auth/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.v1.auth.logout');
    Route::get('/auth/user', [\App\Http\Controllers\Api\V1\AuthController::class, 'user'])->middleware('auth:sanctum')->name('api.v1.auth.user');

    // Protected routes (require authentication + company scoping)
    Route::middleware(['auth:sanctum', 'api.company'])->group(function () {
        // Invoices
        Route::get('/invoices', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'index'])->name('api.v1.invoices.index');
        Route::get('/invoices/{id}', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'show'])->name('api.v1.invoices.show');
        Route::post('/invoices', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'store'])->name('api.v1.invoices.store');
        Route::patch('/invoices/{id}', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'update'])->name('api.v1.invoices.update');
        Route::post('/invoices/{id}/finalize', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'finalize'])->name('api.v1.invoices.finalize');
        Route::get('/invoices/{id}/pdf', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'pdf'])->name('api.v1.invoices.pdf');
        Route::get('/invoices/{id}/export/etims', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'exportEtims'])->name('api.v1.invoices.export-etims');

        // Companies
        Route::get('/companies', [\App\Http\Controllers\Api\V1\CompanyController::class, 'index'])->name('api.v1.companies.index');
        Route::get('/companies/{id}', [\App\Http\Controllers\Api\V1\CompanyController::class, 'show'])->name('api.v1.companies.show');

        // Clients
        Route::get('/clients', [\App\Http\Controllers\Api\V1\ClientController::class, 'index'])->name('api.v1.clients.index');
        Route::get('/clients/{id}', [\App\Http\Controllers\Api\V1\ClientController::class, 'show'])->name('api.v1.clients.show');
    });
});
