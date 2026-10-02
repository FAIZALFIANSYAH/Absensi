<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'role' => 'nullable|in:superadmin,teacher,student',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'active_status' => 'nullable|in:active,inactive',
        ];
    }
}
