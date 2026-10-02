<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StudentRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'nis' => 'required|string|max:50|unique:student_profiles,nis',
            'nik' => 'required|string|max:50|unique:student_profiles,nik',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:2000',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
