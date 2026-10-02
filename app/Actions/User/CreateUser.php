<?php

namespace App\Actions\User;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\QRCode\StudentQrIdentityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function __construct(
        private readonly StudentQrIdentityService $studentQrIdentityService,
    ) {
    }

    public function handle(array $payload, User $actor): User
    {
        return DB::transaction(function () use ($payload, $actor) {
            $approvalStatus = ApprovalStatus::from($payload['approval_status']);
            $role = UserRole::from($payload['role']);

            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'role' => $role,
                'approval_status' => $approvalStatus,
                'is_active' => true,
                'approved_at' => $approvalStatus === ApprovalStatus::Approved ? now() : null,
                'approved_by' => $approvalStatus === ApprovalStatus::Approved ? $actor->id : null,
                'password' => Hash::make($payload['password']),
            ]);

            if ($role === UserRole::Student) {
                StudentProfile::create([
                    'user_id' => $user->id,
                    'nis' => $payload['nis'],
                    'nik' => $payload['nik'],
                    'phone' => $payload['phone'] ?? null,
                    'gender' => $payload['gender'] ?? null,
                    'birth_date' => $payload['birth_date'] ?? null,
                    'address' => $payload['address'] ?? null,
                ]);
            }

            if ($role === UserRole::Teacher) {
                TeacherProfile::create([
                    'user_id' => $user->id,
                    'nip' => $payload['nip'],
                    'nik' => $payload['nik'],
                    'phone' => $payload['phone'] ?? null,
                    'gender' => $payload['gender'] ?? null,
                    'birth_date' => $payload['birth_date'] ?? null,
                    'address' => $payload['address'] ?? null,
                ]);
            }

            if ($approvalStatus === ApprovalStatus::Approved) {
                $this->studentQrIdentityService->ensureForUser($user);
            }

            return $user->fresh(['studentProfile', 'teacherProfile']);
        });
    }
}
