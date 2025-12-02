<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PlatformFeeController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Public\AuthController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\User\CompanyController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no prefix)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');

// AJAX endpoint for invoice preview calculations
Route::post('/api/calculate-invoice-preview', [HomeController::class, 'calculatePreview'])->name('api.calculate-preview')->middleware('throttle:30,1');

// Authentication (public)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showPasswordResetForm'])->name('password.request');
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        // Redirect based on role
        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');

    // Company setup (must be before other routes to catch users without companies)
    Route::get('/company/setup', [CompanyController::class, 'setup'])->name('company.setup');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    // User area (prefix: /app)
    Route::prefix('app')->middleware(\App\Http\Middleware\EnsureUserHasCompany::class)->name('user.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');
        Route::resource('invoices', InvoiceController::class);
        Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
        Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');
        Route::post('/invoices/{id}/send-whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('invoices.send-whatsapp');
        Route::post('/clients', [\App\Http\Controllers\User\ClientController::class, 'store'])->name('clients.store');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/company/settings', [CompanyController::class, 'settings'])->name('company.settings');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update');
        Route::get('/company/invoice-customization', [CompanyController::class, 'invoiceCustomization'])->name('company.invoice-customization');
        Route::post('/company/invoice-format', [CompanyController::class, 'updateInvoiceFormat'])->name('company.update-invoice-format');
        Route::post('/company/invoice-template', [CompanyController::class, 'updateInvoiceTemplate'])->name('company.update-invoice-template');
    });

    // Admin area (prefix: /admin)
    Route::prefix('admin')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, '__invoke'])->name('dashboard');
        Route::resource('companies', \App\Http\Controllers\Admin\CompanyController::class)->except(['create', 'store']);
        Route::resource('clients', ClientController::class);
        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{id}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::resource('users', UserController::class)->except(['create', 'store']);
        Route::get('/platform-fees', [PlatformFeeController::class, 'index'])->name('platform-fees.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
});
