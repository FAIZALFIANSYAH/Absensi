<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
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
        $classes = SchoolClass::query()
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level', 'major', 'academic_year']);

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                'pendingUsers' => User::where('approval_status', ApprovalStatus::Pending)->count(),
                'approvedUsers' => User::where('approval_status', ApprovalStatus::Approved)->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'students' => User::where('role', 'student')->count(),
                'classes' => SchoolClass::count(),
            ],
            'monitoring' => $this->attendanceMonitoringService->buildForClasses($classes, [
                'school_class_id' => request()->query('school_class_id', ''),
                'session_date' => request()->query('session_date', now()->toDateString()),
                'status' => request()->query('status', 'all'),
            ]),
        ]);
    }
}
