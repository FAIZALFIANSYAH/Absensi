<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Validation\Rules;

class TeacherRegisterRequest extends StudentRegisterRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'nip' => 'required|string|max:50|unique:teacher_profiles,nip',
            'nik' => 'required|string|max:50|unique:teacher_profiles,nik',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:2000',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
