<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isStudent = $this->input('role') === 'student';
        $isTeacher = $this->input('role') === 'teacher';

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'role' => 'required|in:teacher,student',
            'approval_status' => 'required|in:pending,approved',
            'password' => ['required', 'confirmed', Password::defaults()],
            'nis' => [Rule::requiredIf($isStudent), 'nullable', 'string', 'max:50', 'unique:student_profiles,nis'],
            'nip' => [Rule::requiredIf($isTeacher), 'nullable', 'string', 'max:50', 'unique:teacher_profiles,nip'],
            'nik' => [Rule::requiredIf($isStudent || $isTeacher), 'nullable', 'string', 'max:50', Rule::unique($isTeacher ? 'teacher_profiles' : 'student_profiles', 'nik')],
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:2000',
        ];
    }
}
