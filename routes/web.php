<?php

namespace App\Http\Controllers\Orders;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;


Route::get('/', function () {
    return view('welcome');
});

Route::prefix('orders')->group(function () {
    Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/store', [OrderController::class, 'store'])->name('orders.store');
});



Route::prefix('invoices')->group(function () {
    Route::get('/generate/{order}', [InvoiceController::class, 'generate'])
        ->name('invoices.generate');

    Route::get('/view/{invoice}', [InvoiceController::class, 'show'])
        ->name('invoices.show');
});
