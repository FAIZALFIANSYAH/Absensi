<?php

namespace App\Actions\Auth;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterStudent
{
    public function handle(array $payload): User
    {
        return DB::transaction(function () use ($payload) {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'role' => UserRole::Student,
                'approval_status' => ApprovalStatus::Pending,
                'password' => Hash::make($payload['password']),
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'nis' => $payload['nis'],
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
