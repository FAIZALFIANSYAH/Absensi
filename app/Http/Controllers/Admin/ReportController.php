<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\Reports\AttendanceReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $attendanceReportService,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AttendanceSession::class);

        return Inertia::render('Admin/Reports/Index', [
            'report' => $this->attendanceReportService->build([
                'school_class_id' => $request->query('school_class_id', ''),
                'session_date' => $request->query('session_date', now()->toDateString()),
                'session_status' => $request->query('session_status', 'all'),
            ]),
        ]);
    }
}
