<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function showDashboard()
    {
        return Inertia::render('Dashboard'); // this matches Dashboard.vue in Pages/
    }
    
    public function showRegistrationForm()
    {
     return Inertia::render('Auth/Register');

    }

    // Handle user registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // default role
        ]);

        Auth::login($user);

        return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
    }

    // Show login form
    public function showLoginForm()
    {
        return Inertia::render('auth.login'); 
    }

    // Handle user login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['message' => 'Login successful', 'user' => Auth::user()]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Handle user logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
