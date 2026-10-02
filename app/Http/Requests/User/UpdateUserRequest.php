<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $role = $user->role;

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'nis' => [
                Rule::requiredIf($role === UserRole::Student),
                'nullable',
                'string',
                'max:50',
                Rule::unique('student_profiles', 'nis')->ignore($user->studentProfile?->id),
            ],
            'nip' => [
                Rule::requiredIf($role === UserRole::Teacher),
                'nullable',
                'string',
                'max:50',
                Rule::unique('teacher_profiles', 'nip')->ignore($user->teacherProfile?->id),
            ],
            'nik' => [
                Rule::requiredIf(in_array($role, [UserRole::Student, UserRole::Teacher], true)),
                'nullable',
                'string',
                'max:50',
                $role === UserRole::Teacher
                    ? Rule::unique('teacher_profiles', 'nik')->ignore($user->teacherProfile?->id)
                    : Rule::unique('student_profiles', 'nik')->ignore($user->studentProfile?->id),
            ],
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:2000',
        ];
    }
}
