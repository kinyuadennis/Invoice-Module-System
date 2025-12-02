<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationEmail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        // Send verification email via queue
        SendVerificationEmail::dispatch($user, $request->ip());

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

        // Rate limiting: 3 per hour, 5 per 24 hours
        $hourlyKey = "verification_resend:{$user->id}:hourly";
        $dailyKey = "verification_resend:{$user->id}:daily";

        $hourlyCount = Cache::get($hourlyKey, 0);
        $dailyCount = Cache::get($dailyKey, 0);

        if ($hourlyCount >= 3) {
            $nextAllowed = Cache::get("{$hourlyKey}:next", now()->addHour());

            return back()->withErrors([
                'email' => 'Too many verification emails sent. Please try again in '.now()->diffForHumans($nextAllowed, true).'.',
            ]);
        }

        if ($dailyCount >= 5) {
            $nextAllowed = Cache::get("{$dailyKey}:next", now()->addDay());

            return back()->withErrors([
                'email' => 'Daily limit reached. Please try again in '.now()->diffForHumans($nextAllowed, true).'.',
            ]);
        }

        // Increment counters
        Cache::put($hourlyKey, $hourlyCount + 1, now()->addHour());
        Cache::put($dailyKey, $dailyCount + 1, now()->addDay());
        Cache::put("{$hourlyKey}:next", now()->addHour(), now()->addHour());
        Cache::put("{$dailyKey}:next", now()->addDay(), now()->addDay());

        // Dispatch job to send verification email
        SendVerificationEmail::dispatch($user, $request->ip());

        return back()->with('status', 'Verification link sent!');
    }

    /**
     * Verify the user's email address using token.
     */
    public function verifyEmail(Request $request, string $token)
    {
        $verification = EmailVerification::where('token', $token)
            ->valid()
            ->first();

        if (! $verification) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'Invalid or expired verification link.']);
        }

        $user = $verification->user;

        if ($user->hasVerifiedEmail()) {
            $verification->markAsUsed();

            return redirect()->route('user.dashboard')
                ->with('status', 'Email already verified.');
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Mark token as used
        $verification->markAsUsed();

        // Clear rate limit cache
        Cache::forget("verification_resend:{$user->id}:hourly");
        Cache::forget("verification_resend:{$user->id}:daily");

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

        // Redirect to company setup if user doesn't have a company
        if (! $user->company_id) {
            return redirect()->route('company.setup')
                ->with('status', 'Email verified successfully! Please complete your company setup.');
        }

        return redirect()->route('user.dashboard')
            ->with('status', 'Email verified successfully!');
    }
}
