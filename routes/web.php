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

// Webhooks (no CSRF protection needed)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/stripe', [\App\Http\Controllers\Webhook\PaymentWebhookController::class, 'stripe'])->name('stripe');
    Route::post('/mpesa/callback', [\App\Http\Controllers\Webhook\PaymentWebhookController::class, 'mpesa'])->name('mpesa');
});

// Customer Portal (token-based access, no authentication required)
Route::prefix('invoice')->name('customer.invoices.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\Customer\InvoiceController::class, 'show'])->name('show');
    Route::get('/{token}/pdf', [\App\Http\Controllers\Customer\InvoiceController::class, 'downloadPdf'])->name('pdf');
    Route::post('/{token}/pay/stripe', [\App\Http\Controllers\Customer\InvoiceController::class, 'payStripe'])->name('pay.stripe');
    Route::post('/{token}/pay/mpesa', [\App\Http\Controllers\Customer\InvoiceController::class, 'payMpesa'])->name('pay.mpesa');
    Route::get('/{token}/payment-status', [\App\Http\Controllers\Customer\InvoiceController::class, 'paymentStatus'])->name('payment-status');
});

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
Route::get('/testimonials', [\App\Http\Controllers\Public\ReviewController::class, 'publicIndex'])->name('testimonials');

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

        // Feedback
        Route::post('/feedback', [\App\Http\Controllers\User\FeedbackController::class, 'store'])->name('feedback.store');

        // Invoice routes - must be before resource route to avoid conflicts
        Route::post('/invoices/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('/invoices/preview-frame', [InvoiceController::class, 'previewFrame'])->name('invoices.preview-frame');
        Route::get('/invoices/{invoice}/preview-frame', [InvoiceController::class, 'previewFrameFromInvoice'])->name('invoices.preview-frame-from-invoice');
        Route::post('/invoices/autosave', [InvoiceController::class, 'autosave'])->name('invoices.autosave');

        Route::resource('invoices', InvoiceController::class);
        Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
        Route::post('/invoices/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');

        // Estimate routes
        Route::resource('estimates', \App\Http\Controllers\User\EstimateController::class);
        Route::post('/estimates/{id}/convert', [\App\Http\Controllers\User\EstimateController::class, 'convert'])->name('estimates.convert');
        Route::post('/estimates/{id}/send', [\App\Http\Controllers\User\EstimateController::class, 'send'])->name('estimates.send');
        Route::get('/estimates/{id}/pdf', [\App\Http\Controllers\User\EstimateController::class, 'pdf'])->name('estimates.pdf');

        // Expense routes
        Route::resource('expenses', \App\Http\Controllers\User\ExpenseController::class);

        // Credit Note routes
        Route::resource('credit-notes', \App\Http\Controllers\User\CreditNoteController::class);
        Route::post('/credit-notes/{id}/issue', [\App\Http\Controllers\User\CreditNoteController::class, 'issue'])->name('credit-notes.issue');
        Route::post('/credit-notes/{id}/apply-to-invoice', [\App\Http\Controllers\User\CreditNoteController::class, 'applyToInvoice'])->name('credit-notes.apply-to-invoice');
        Route::post('/credit-notes/{id}/submit-etims', [\App\Http\Controllers\User\CreditNoteController::class, 'submitToEtims'])->name('credit-notes.submit-etims');
        Route::get('/credit-notes/{id}/pdf', [\App\Http\Controllers\User\CreditNoteController::class, 'pdf'])->name('credit-notes.pdf');
        Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');
        Route::post('/invoices/{id}/send-whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('invoices.send-whatsapp');
        Route::post('/invoices/{id}/record-payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');

        // Template routes
        Route::post('/invoices/save-as-template', [InvoiceController::class, 'saveAsTemplate'])->name('invoices.save-as-template');
        Route::get('/invoices/templates', [InvoiceController::class, 'getTemplates'])->name('invoices.templates');
        Route::get('/invoices/templates/{id}/load', [InvoiceController::class, 'loadTemplate'])->name('invoices.templates.load');
        Route::delete('/invoices/templates/{id}', [InvoiceController::class, 'deleteTemplate'])->name('invoices.templates.delete');
        Route::post('/invoices/templates/{id}/toggle-favorite', [InvoiceController::class, 'toggleFavorite'])->name('invoices.templates.toggle-favorite');

        // Recurring invoices
        Route::resource('recurring-invoices', \App\Http\Controllers\User\RecurringInvoiceController::class);
        Route::post('/recurring-invoices/{recurringInvoice}/pause', [\App\Http\Controllers\User\RecurringInvoiceController::class, 'pause'])->name('recurring-invoices.pause');
        Route::post('/recurring-invoices/{recurringInvoice}/resume', [\App\Http\Controllers\User\RecurringInvoiceController::class, 'resume'])->name('recurring-invoices.resume');
        Route::post('/recurring-invoices/{recurringInvoice}/cancel', [\App\Http\Controllers\User\RecurringInvoiceController::class, 'cancel'])->name('recurring-invoices.cancel');
        Route::post('/recurring-invoices/{recurringInvoice}/generate', [\App\Http\Controllers\User\RecurringInvoiceController::class, 'generate'])->name('recurring-invoices.generate');

        // Reports
        Route::get('/reports', [\App\Http\Controllers\User\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/revenue', [\App\Http\Controllers\User\ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/invoices', [\App\Http\Controllers\User\ReportController::class, 'invoices'])->name('reports.invoices');
        Route::get('/reports/payments', [\App\Http\Controllers\User\ReportController::class, 'payments'])->name('reports.payments');
        Route::get('/reports/export/invoices-csv', [\App\Http\Controllers\User\ReportController::class, 'exportInvoicesCsv'])->name('reports.export.invoices-csv');
        Route::get('/reports/export/revenue-csv', [\App\Http\Controllers\User\ReportController::class, 'exportRevenueCsv'])->name('reports.export.revenue-csv');

        // Payment Gateway
        Route::post('/invoices/{invoice}/pay/stripe', [\App\Http\Controllers\User\PaymentGatewayController::class, 'initiateStripe'])->name('invoices.pay.stripe');
        Route::post('/invoices/{invoice}/pay/mpesa', [\App\Http\Controllers\User\PaymentGatewayController::class, 'initiateMpesa'])->name('invoices.pay.mpesa');
        Route::get('/invoices/{invoice}/payment-status', [\App\Http\Controllers\User\PaymentGatewayController::class, 'checkStatus'])->name('invoices.payment-status');

        // eTIMS Export
        Route::get('/invoices/{invoice}/etims/export', [\App\Http\Controllers\User\EtimsController::class, 'export'])->name('invoices.etims.export');
        Route::post('/invoices/{invoice}/etims/generate-qr', [\App\Http\Controllers\User\EtimsController::class, 'generateQrCode'])->name('invoices.etims.generate-qr');
        Route::post('/invoices/{invoice}/etims/submit', [\App\Http\Controllers\User\EtimsController::class, 'submit'])->name('invoices.etims.submit');

        Route::post('/clients', [\App\Http\Controllers\User\ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/search', [\App\Http\Controllers\User\ClientController::class, 'search'])->name('clients.search');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
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
        Route::get('/system-settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('system-settings.index');
        Route::put('/system-settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('system-settings.update');
        Route::resource('audit-logs', \App\Http\Controllers\Admin\AuditLogController::class)->only(['index', 'show']);
        Route::get('/billing/plans', [\App\Http\Controllers\Admin\BillingController::class, 'plans'])->name('billing.plans');
        Route::get('/billing/subscriptions', [\App\Http\Controllers\Admin\BillingController::class, 'subscriptions'])->name('billing.subscriptions');
        Route::get('/billing/subscriptions/{id}', [\App\Http\Controllers\Admin\BillingController::class, 'showSubscription'])->name('billing.subscriptions.show');
        Route::get('/billing/history', [\App\Http\Controllers\Admin\BillingController::class, 'history'])->name('billing.history');
        Route::get('/system-settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('system-settings.index');
        Route::put('/system-settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('system-settings.update');
        Route::resource('audit-logs', \App\Http\Controllers\Admin\AuditLogController::class)->only(['index', 'show']);
    });
});
