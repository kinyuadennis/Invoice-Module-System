<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // Show user profile
    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        return response()->json($user);
    }

    // Update user profile and roles
    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->updateUser($id, $request->validated());
        return response()->json(['user' => $user, 'message' => 'User updated successfully']);
    }

    // Other actions like list users, create user etc can be added similarly
}
