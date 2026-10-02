<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceMonitoringService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AttendanceMonitoringService $attendanceMonitoringService,
    ) {
    }

    public function __invoke(): Response
    {
        $user = request()->user()->load([
            'teacherProfile',
            'schoolClasses' => fn ($query) => $query->orderBy('name'),
        ]);
        $profile = $user->teacherProfile;
        $classes = $user->schoolClasses;

        return Inertia::render('Teacher/Dashboard/Index', [
            'profile' => $profile ? [
                'nip' => $profile->nip,
                'nik' => $profile->nik,
                'phone' => $profile->phone,
                'gender' => $profile->gender,
                'birth_date' => $profile->birth_date?->format('Y-m-d'),
                'address' => $profile->address,
            ] : null,
            'classes' => $classes->map(fn ($schoolClass) => [
                'id' => $schoolClass->id,
                'name' => $schoolClass->name,
                'grade_level' => $schoolClass->grade_level,
                'major' => $schoolClass->major,
                'academic_year' => $schoolClass->academic_year,
                'member_role' => $schoolClass->pivot?->member_role,
            ])->values(),
            'monitoring' => $this->attendanceMonitoringService->buildForClasses($classes, [
                'school_class_id' => request()->query('school_class_id', ''),
                'session_date' => request()->query('session_date', now()->toDateString()),
                'status' => request()->query('status', 'all'),
            ]),
        ]);
    }
}
