<?php

namespace App\Actions\Auth;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterTeacher
{
    public function handle(array $payload): User
    {
        return DB::transaction(function () use ($payload) {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'role' => UserRole::Teacher,
                'approval_status' => ApprovalStatus::Pending,
                'password' => Hash::make($payload['password']),
            ]);

            TeacherProfile::create([
                'user_id' => $user->id,
                'nip' => $payload['nip'],
                'nik' => $payload['nik'],
                'phone' => $payload['phone'] ?? null,
                'gender' => $payload['gender'] ?? null,
                'birth_date' => $payload['birth_date'] ?? null,
                'address' => $payload['address'] ?? null,
            ]);

            return $user;
        });
    }
}
