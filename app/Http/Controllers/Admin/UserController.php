<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $query = User::with('company');

        // Company filter
        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        $users = $query->latest()
            ->paginate(15)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'company' => $user->company ? [
                        'id' => $user->company->id,
                        'name' => $user->company->name,
                    ] : null,
                ];
            });

        $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);

        return view('admin.users.index', [
            'users' => $users,
            'companies' => $companies,
            'filters' => $request->only(['company_id']),
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent changing admin role if it's the last admin
        if ($request->has('role') && $user->role === 'admin' && $request->role !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors([
                    'role' => 'Cannot remove the last admin user.',
                ]);
            }
        }

        // Prevent changing your own role
        if ($user->id === auth()->id() && $request->has('role') && $request->role !== auth()->user()->role) {
            return back()->withErrors([
                'role' => 'You cannot change your own role.',
            ]);
        }

        $oldValues = $user->toArray();
        $user = $this->userService->updateUser($id, $request->validated());
        $newValues = $user->fresh()->toArray();

        // Log the user update
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Updated user {$user->name} (ID: {$user->id})",
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'message' => 'You cannot delete your own account.',
            ]);
        }

        // Prevent deleting the last admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors([
                    'message' => 'Cannot delete the last admin user.',
                ]);
            }
        }

        $userData = $user->toArray();
        $user->delete();

        // Log the user deletion
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model_type' => User::class,
            'model_id' => $id,
            'description' => "Deleted user {$userData['name']} (ID: {$id})",
            'old_values' => $userData,
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
