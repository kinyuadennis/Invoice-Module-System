<?php

namespace App\Http\Controllers\Orders;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');

Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:5,1');  // password reset email requests throttled

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    // admin protected routes
});



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Email verification notice (ask user to verify)
    Route::get('/email/verify', function () {
        return view('auth.verify-email'); // create this blade or Vue page to remind users
    })->middleware('auth')->name('verification.notice');

    // Email verification handler (verifies user)
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // marks email as verified
        return redirect('/dashboard'); // or wherever you want after verification
    })->middleware(['auth', 'signed'])->name('verification.verify');

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    // other protected routes that require verified email...
});




// Route::prefix('orders')->group(function () {
//     Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
//     Route::post('/store', [OrderController::class, 'store'])->name('orders.store');
// });



// Route::prefix('invoices')->group(function () {
//     Route::get('/generate/{order}', [InvoiceController::class, 'generate'])
//         ->name('invoices.generate');

//     Route::get('/view/{invoice}', [InvoiceController::class, 'show'])
//         ->name('invoices.show');
// });
