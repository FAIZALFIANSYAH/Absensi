<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    public function handle(User $user, array $payload): User
    {
        return DB::transaction(function () use ($user, $payload) {
            $user->update([
                'name' => $payload['name'],
                'email' => $payload['email'],
            ]);

            if ($user->role === UserRole::Student) {
                $user->studentProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nis' => $payload['nis'],
                        'nik' => $payload['nik'],
                        'phone' => $payload['phone'] ?? null,
                        'gender' => $payload['gender'] ?? null,
                        'birth_date' => $payload['birth_date'] ?? null,
                        'address' => $payload['address'] ?? null,
                    ]
                );
            }

            if ($user->role === UserRole::Teacher) {
                $user->teacherProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip' => $payload['nip'],
                        'nik' => $payload['nik'],
                        'phone' => $payload['phone'] ?? null,
                        'gender' => $payload['gender'] ?? null,
                        'birth_date' => $payload['birth_date'] ?? null,
                        'address' => $payload['address'] ?? null,
                    ]
                );
            }

            return $user->fresh(['studentProfile', 'teacherProfile']);
        });
    }
}
