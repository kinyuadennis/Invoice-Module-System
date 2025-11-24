<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        // Add authorization logic if needed, otherwise allow all for now
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->route('id'),
            'password' => 'sometimes|nullable|min:8|confirmed',
            'role' => 'sometimes|required|in:admin,user,client',
        ];
    }
}
