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
use Illuminate\Support\Facades\Route;

// Public routes (no prefix)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');

// Favicon route - return 204 No Content to prevent errors
Route::get('/favicon.ico', function () {
    return response('', 204)->header('Content-Type', 'image/x-icon');
})->name('favicon');

// AJAX endpoint for invoice preview calculations
Route::post('/api/calculate-invoice-preview', [HomeController::class, 'calculatePreview'])->name('api.calculate-preview')->middleware('throttle:30,1');

// Public API endpoint for reviews
Route::get('/api/reviews', [\App\Http\Controllers\Public\ReviewController::class, 'index'])->name('api.reviews');

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

// Email verification (accessible without auth - uses session for pending users)
Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
Route::get('/email/verify/check', [AuthController::class, 'checkVerificationStatus'])->name('verification.check');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:6,1')
    ->name('verification.send');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Company setup (requires email verification)
    Route::middleware('verified')->group(function () {
        Route::get('/company/setup', [CompanyController::class, 'setup'])->name('company.setup');
        Route::post('/company', [CompanyController::class, 'store'])->name('company.store');
    });

    // Onboarding wizard (requires email verification, but NOT company)
    Route::middleware('verified')->group(function () {
        Route::get('/onboarding', [\App\Http\Controllers\User\OnboardingController::class, 'index'])->name('user.onboarding.index');
        Route::get('/onboarding/step/{step}', [\App\Http\Controllers\User\OnboardingController::class, 'showStep'])->name('user.onboarding.step');
        Route::post('/onboarding', [\App\Http\Controllers\User\OnboardingController::class, 'store'])->name('user.onboarding.store');
        Route::post('/onboarding/complete', [\App\Http\Controllers\User\OnboardingController::class, 'complete'])->name('user.onboarding.complete');
    });

    // User area (prefix: /app)
    Route::prefix('app')->middleware(['verified', \App\Http\Middleware\EnsureUserHasCompany::class, \App\Http\Middleware\EnsureActiveCompany::class])->name('user.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');

        // Invoice routes - must be before resource route to avoid conflicts
        Route::post('/invoices/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('/invoices/preview-frame', [InvoiceController::class, 'previewFrame'])->name('invoices.preview-frame');
        Route::get('/invoices/{invoice}/preview-frame', [InvoiceController::class, 'previewFrameFromInvoice'])->name('invoices.preview-frame-from-invoice');
        Route::post('/invoices/autosave', [InvoiceController::class, 'autosave'])->name('invoices.autosave');

        Route::resource('invoices', InvoiceController::class);
        Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
        Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');
        Route::post('/invoices/{id}/send-whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('invoices.send-whatsapp');

        Route::post('/clients', [\App\Http\Controllers\User\ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/search', [\App\Http\Controllers\User\ClientController::class, 'search'])->name('clients.search');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        // Company management routes
        Route::resource('companies', \App\Http\Controllers\User\CompanyManagementController::class);
        Route::post('/company/switch', [\App\Http\Controllers\User\CompanyManagementController::class, 'switchCompany'])->name('company.switch');
        Route::get('/company/{id}/details', [\App\Http\Controllers\User\CompanyManagementController::class, 'getCompanyDetails'])->name('company.details');

        Route::get('/company/settings', [CompanyController::class, 'settings'])->name('company.settings');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update');
        Route::get('/company/invoice-customization', [CompanyController::class, 'invoiceCustomization'])->name('company.invoice-customization');
        Route::post('/company/invoice-format', [CompanyController::class, 'updateInvoiceFormat'])->name('company.update-invoice-format');
        Route::post('/company/invoice-template', [CompanyController::class, 'updateInvoiceTemplate'])->name('company.update-invoice-template');
        Route::get('/company/invoice-template/preview', [CompanyController::class, 'previewTemplate'])->name('company.invoice-template.preview');
        Route::post('/company/branding', [CompanyController::class, 'updateBranding'])->name('company.update-branding');
        Route::post('/company/advanced-styling', [CompanyController::class, 'updateAdvancedStyling'])->name('company.update-advanced-styling');

        // Item search for autocomplete
        Route::get('/items/search', [\App\Http\Controllers\User\ItemController::class, 'search'])->name('items.search');

        // Payment Methods
        Route::post('/company/payment-methods', [\App\Http\Controllers\User\CompanyPaymentMethodController::class, 'store'])->name('company.payment-methods.store');
        Route::put('/company/payment-methods/{paymentMethod}', [\App\Http\Controllers\User\CompanyPaymentMethodController::class, 'update'])->name('company.payment-methods.update');
        Route::delete('/company/payment-methods/{paymentMethod}', [\App\Http\Controllers\User\CompanyPaymentMethodController::class, 'destroy'])->name('company.payment-methods.destroy');
        Route::post('/company/payment-methods/reorder', [\App\Http\Controllers\User\CompanyPaymentMethodController::class, 'reorder'])->name('company.payment-methods.reorder');
    });

    // Admin area (prefix: /admin)
    Route::prefix('admin')->middleware(['verified', 'role:admin'])->name('admin.')->group(function () {
        // Simple /admin route redirects to dashboard
        Route::get('/', function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (! $user) {
                return redirect()->route('login')->with('error', 'Please log in to access the admin area.');
            }
            if ($user->role !== 'admin') {
                abort(403, 'Unauthorized. Your role is: '.($user->role ?? 'not set').'. Required role: admin');
            }

            return redirect()->route('admin.dashboard');
        })->name('index');
        Route::get('/dashboard', [AdminDashboardController::class, '__invoke'])->name('dashboard');
        Route::resource('companies', \App\Http\Controllers\Admin\CompanyController::class)->except(['create', 'store']);
        Route::resource('clients', ClientController::class);
        Route::resource('reviews', \App\Http\Controllers\Admin\ReviewController::class);
        Route::post('/reviews/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{id}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::resource('users', UserController::class)->except(['create', 'store']);
        Route::get('/platform-fees', [PlatformFeeController::class, 'index'])->name('platform-fees.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
});
