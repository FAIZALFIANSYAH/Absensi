<?php

namespace App\Http\Requests\Class;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',
            'major' => 'nullable|string|max:100',
            'academic_year' => 'required|string|max:50',
            'homeroom_teacher_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', UserRole::Teacher->value)
                    ->where('approval_status', ApprovalStatus::Approved->value)),
            ],
        ];
    }
}
