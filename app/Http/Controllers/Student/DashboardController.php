<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\QRCode\StudentQrIdentityService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StudentQrIdentityService $studentQrIdentityService,
    ) {
    }

    public function __invoke(): Response
    {
        /** @var \App\Models\User $user */
        $user = request()->user()->load(['studentProfile', 'schoolClasses']);
        $profile = $this->studentQrIdentityService->ensureForUser($user);

        return Inertia::render('Student/Dashboard/Index', [
            'profile' => $profile ? [
                'nis' => $profile->nis,
                'nik' => $profile->nik,
                'phone' => $profile->phone,
                'gender' => $profile->gender,
                'birth_date' => $profile->birth_date?->format('Y-m-d'),
                'address' => $profile->address,
                'qr_token' => $profile->qr_token,
                'qr_generated_at' => $profile->qr_generated_at?->toIso8601String(),
                'qr_payload' => $this->studentQrIdentityService->payload($profile),
            ] : null,
            'classes' => $user->schoolClasses->map(fn ($schoolClass) => [
                'id' => $schoolClass->id,
                'name' => $schoolClass->name,
                'grade_level' => $schoolClass->grade_level,
                'major' => $schoolClass->major,
                'academic_year' => $schoolClass->academic_year,
                'member_role' => $schoolClass->pivot?->member_role,
            ])->values(),
        ]);
    }
}
