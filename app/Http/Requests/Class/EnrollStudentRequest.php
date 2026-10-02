<?php

namespace App\Http\Requests\Class;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', UserRole::Student->value)
                    ->where('approval_status', ApprovalStatus::Approved->value)),
            ],
        ];
    }
}
