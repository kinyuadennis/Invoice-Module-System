<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = CurrentCompanyService::get();

        return view('user.profile.index', [
            'user' => $user,
            'company' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.Auth::id(),
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:max_width=2000,max_height=2000',
        ]);

        $user = Auth::user();

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $validated['profile_photo_path'] = $path;
        }

        // Remove profile_photo from validated array (it's not a database column)
        unset($validated['profile_photo']);

        $user->update($validated);

        return redirect()->route('user.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->route('user.profile')
            ->with('success', 'Profile photo deleted successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('user.profile')
            ->with('success', 'Password updated successfully.');
    }
}
