<?php

namespace App\Http\Controllers\Public;

use App\Config\PaymentConstants;
use App\Config\SubscriptionConstants;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'nullable|string|max:255',
        ]);

        // Handle plan parameter (from query string: ?plan=slug or ?plan_id=id)
        $planId = null;
        $planSlug = $request->query('plan');
        $planIdParam = $request->query('plan_id');

        if ($planIdParam) {
            $planId = $planIdParam;
        } elseif ($planSlug) {
            $plan = SubscriptionPlan::where('slug', $planSlug)->where('is_active', true)->first();
            if ($plan) {
                $planId = $plan->id;
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'email_verified_at' => null, // Explicitly set to null
        ]);

        // Create company if company name provided during registration
        $company = null;
        if (! empty($validated['company_name'])) {
            $company = \App\Models\Company::create([
                'owner_user_id' => $user->id,
                'name' => $validated['company_name'],
                'currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'invoice_prefix' => 'INV',
                'next_invoice_sequence' => 1,
            ]);

            // Set as active company
            \Illuminate\Support\Facades\Session::put('active_company_id', $company->id);
            $user->update([
                'active_company_id' => $company->id,
                'company_id' => $company->id, // Legacy compatibility
            ]);

            // Create default invoice prefix
            $prefixService = app(\App\Services\InvoicePrefixService::class);
            $prefixService->createDefaultPrefix($company, $user->id);

            // Activate free plan for new users (if company was created)
            // Per Subscription model invariant: A subscription cannot be ACTIVE without at least one successful Payment
            $freePlan = SubscriptionPlan::where('slug', 'free')->where('is_active', true)->first();
            if ($freePlan && $company) {
                $subscriptionService = app(\App\Services\SubscriptionService::class);
                $subscriptionRepository = app(\App\Subscriptions\Repositories\SubscriptionRepository::class);

                \Illuminate\Support\Facades\DB::beginTransaction();

                try {
                    // Create subscription with PENDING status (will be activated after payment is created)
                    $subscription = $subscriptionRepository->create([
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                        'subscription_plan_id' => $freePlan->id,
                        'plan_code' => $freePlan->slug,
                        'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_PENDING,
                        'gateway' => PaymentConstants::GATEWAY_STRIPE, // Gateway field is immutable, set it now
                        'starts_at' => now(),
                        'ends_at' => null, // Free plan doesn't expire
                        'auto_renew' => false,
                    ]);

                    // Create a successful payment record for the free subscription (amount = 0)
                    // This satisfies the invariant: "A subscription cannot be ACTIVE without at least one successful Payment"
                    $payment = \App\Models\Payment::create([
                        'company_id' => $company->id,
                        'payable_type' => \App\Models\Subscription::class,
                        'payable_id' => $subscription->id,
                        'amount' => 0, // Free plan has zero cost
                        'gateway' => PaymentConstants::GATEWAY_STRIPE,
                        'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                        'payment_date' => now(),
                        'paid_at' => now(),
                        'gateway_transaction_id' => 'FREE-'.strtoupper(\Illuminate\Support\Str::uuid()),
                        'gateway_metadata' => [
                            'type' => 'free_plan_activation',
                            'plan_code' => $freePlan->slug,
                        ],
                    ]);

                    // Activate subscription using the proper state machine method
                    // This enforces all invariants and creates invoice snapshot
                    $subscriptionService->activateSubscription($payment);

                    \Illuminate\Support\Facades\DB::commit();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    \Illuminate\Support\Facades\Log::error('Failed to activate free subscription for new user', [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                        'plan_id' => $freePlan->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't throw - allow registration to continue even if free subscription activation fails
                }
            }
        }

        // Store plan ID in session for redirect after verification (if plan was selected and not free)
        if ($planId) {
            $selectedPlan = SubscriptionPlan::find($planId);
            // Only store if it's not the free plan (free plan is auto-activated above)
            if ($selectedPlan && $selectedPlan->slug !== 'free') {
                $request->session()->put('pending_subscription_plan', $planId);
            }
        }

        // Send verification email using Laravel's standard method
        try {
            $user->sendEmailVerificationNotification();

            // In development, store the verification URL in session for easy access
            if (config('app.env') === 'local' || config('app.debug')) {
                $verificationUrl = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addHours(24),
                    [
                        'id' => $user->getKey(),
                        'hash' => sha1($user->getEmailForVerification()),
                    ]
                );
                $request->session()->put('dev_verification_url', $verificationUrl);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send verification email during registration', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            // Still redirect to verification page - user can resend
            return redirect()->route('verification.notice')
                ->with('error', 'Registration successful, but we encountered an issue sending the verification email. Please use the resend button below.');
        }

        // Store user ID in session for verification page access
        $request->session()->put('pending_verification_user_id', $user->id);

        // Redirect to verification notice (user is NOT logged in yet)
        return redirect()->route('verification.notice')
            ->with('status', 'Registration successful! Please check your email to verify your account.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if email is verified - if not, redirect to verification notice
            if (! $user->hasVerifiedEmail()) {
                // Store user ID in session for verification page access
                $request->session()->put('pending_verification_user_id', $user->id);

                return redirect()->route('verification.notice')
                    ->with('status', 'Please verify your email address to continue.');
            }

            // Redirect to company setup if user doesn't have a company
            if (! $user->company_id) {
                return redirect()->route('company.setup');
            }

            // Redirect based on user role
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showPasswordResetForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->input('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Get the user to auto-login
            $user = User::where('email', $request->email)->first();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();

                // Redirect based on user role
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard')->with('status', __($status));
                }

                return redirect()->route('user.dashboard')->with('status', __($status));
            }
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Show the email verification notice page.
     */
    public function showVerificationNotice(Request $request)
    {
        // Allow access if user is authenticated OR has pending verification in session
        $userId = Auth::id() ?? $request->session()->get('pending_verification_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->with('status', 'Please log in to verify your email.');
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('pending_verification_user_id');

            return redirect()->route('login')
                ->with('status', 'User not found. Please register again.');
        }

        return view('auth.verify-email', ['user' => $user]);
    }

    /**
     * Check verification status (for polling).
     */
    public function checkVerificationStatus(Request $request)
    {
        $userId = Auth::id() ?? $request->session()->get('pending_verification_user_id');

        if (! $userId) {
            return response()->json(['verified' => false]);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['verified' => false]);
        }

        // Refresh user to get latest verification status
        $user->refresh();

        if ($user->hasVerifiedEmail()) {
            // Clear session
            $request->session()->forget('pending_verification_user_id');

            // Determine redirect URL
            $redirect = route('user.dashboard');
            if ($user->role === 'admin') {
                $redirect = route('admin.dashboard');
            } elseif (! $user->onboarding_completed) {
                $redirect = route('user.onboarding.index');
            } elseif (! $user->company_id) {
                $redirect = route('company.setup');
            }

            return response()->json([
                'verified' => true,
                'redirect' => $redirect,
            ]);
        }

        return response()->json(['verified' => false]);
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(Request $request)
    {
        // Get user from auth or session
        $user = $request->user();

        if (! $user) {
            $userId = $request->session()->get('pending_verification_user_id');
            if (! $userId) {
                return redirect()->route('login')
                    ->with('status', 'Please log in to resend verification email.');
            }
            $user = User::find($userId);
            if (! $user) {
                $request->session()->forget('pending_verification_user_id');

                return redirect()->route('login')
                    ->with('status', 'User not found. Please register again.');
            }
        }

        if ($user->hasVerifiedEmail()) {
            // Clear session if exists
            $request->session()->forget('pending_verification_user_id');

            // Log in if not already logged in
            if (! Auth::check()) {
                Auth::login($user);
            }

            return redirect()->route('user.dashboard');
        }

        // Send verification email using Laravel's standard method
        try {
            $user->sendEmailVerificationNotification();

            // In development, store the verification URL in session for easy access
            if (config('app.env') === 'local' || config('app.debug')) {
                $verificationUrl = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addHours(24),
                    [
                        'id' => $user->getKey(),
                        'hash' => sha1($user->getEmailForVerification()),
                    ]
                );
                $request->session()->put('dev_verification_url', $verificationUrl);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send verification email during resend', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Failed to send verification email. Please check your email configuration or try again later.',
            ]);
        }

        return back()->with('status', 'Verification link sent! Please check your email.');
    }

    /**
     * Verify the user's email address using signed URL.
     */
    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        // Verify the hash matches the user's email
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'Invalid verification link.']);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            // Clear pending verification session
            $request->session()->forget('pending_verification_user_id');

            // Log in if not already logged in
            if (! Auth::check()) {
                Auth::login($user);
            }

            return redirect()->route('user.dashboard')
                ->with('status', 'Email already verified.');
        }

        // Mark email as verified and fire verified event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Clear pending verification session
        $request->session()->forget('pending_verification_user_id');

        // Log the user in (they weren't logged in before verification)
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect based on role and company status
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Email verified successfully!');
        }

        // Redirect to onboarding if not completed, otherwise to company setup or dashboard
        if (! $user->onboarding_completed) {
            return redirect()->route('user.onboarding.index')
                ->with('status', 'Email verified successfully! Let\'s get you set up.');
        }

        // Redirect to company setup if user doesn't have a company
        if (! $user->company_id) {
            return redirect()->route('company.setup')
                ->with('status', 'Email verified successfully! Please complete your company setup.');
        }

        // Check if user registered with a plan selected (redirect to checkout)
        $pendingPlanId = $request->session()->get('pending_subscription_plan');
        if ($pendingPlanId) {
            $request->session()->forget('pending_subscription_plan');

            return redirect()->route('user.subscriptions.checkout', ['plan' => $pendingPlanId])
                ->with('status', 'Email verified successfully! Complete your subscription setup.');
        }

        return redirect()->route('user.dashboard')
            ->with('status', 'Email verified successfully!');
    }
}
