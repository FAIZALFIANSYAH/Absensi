<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Attendance\OpenAttendanceSession;
use App\Actions\Attendance\RecordAttendanceScan;
use App\Actions\Attendance\CloseAttendanceSession;
use App\Enums\ClassMemberRole;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CloseAttendanceSessionRequest;
use App\Http\Requests\Attendance\OpenAttendanceSessionRequest;
use App\Http\Requests\Attendance\ScanAttendanceRequest;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Services\Attendance\AttendanceSummaryService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly OpenAttendanceSession $openAttendanceSession,
        private readonly RecordAttendanceScan $recordAttendanceScan,
        private readonly CloseAttendanceSession $closeAttendanceSession,
        private readonly AttendanceSummaryService $attendanceSummaryService,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', AttendanceSession::class);

        return Inertia::render('Admin/Attendance/Index', $this->payload());
    }

    public function store(OpenAttendanceSessionRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($request->integer('school_class_id'));
        $this->authorize('create', [AttendanceSession::class, $schoolClass]);

        $this->openAttendanceSession->handle($schoolClass, $request->user(), [
            'session_date' => $request->input('session_date'),
            'start_time' => $request->input('start_time'),
            'notes' => $request->string('notes')->toString() ?: null,
        ]);

        return back()->with('status', 'Sesi absensi berhasil dibuka.');
    }

    public function scan(ScanAttendanceRequest $request): RedirectResponse
    {
        $session = AttendanceSession::with('schoolClass')->findOrFail($request->integer('attendance_session_id'));
        $this->authorize('scan', $session);

        $this->recordAttendanceScan->handle($session, $request->user(), [
            'qr_token' => $request->string('qr_token')->toString(),
            'status' => $request->input('status'),
            'notes' => $request->string('notes')->toString() ?: null,
        ]);

        return back()->with('status', 'Scan absensi berhasil direkam.');
    }

    public function close(CloseAttendanceSessionRequest $request): RedirectResponse
    {
        $session = AttendanceSession::findOrFail($request->integer('attendance_session_id'));
        $this->authorize('close', $session);

        $this->closeAttendanceSession->handle($session);

        return back()->with('status', 'Sesi absensi berhasil ditutup.');
    }

    private function payload(): array
    {
        return [
            'classes' => SchoolClass::query()
                ->with(['members.user:id,name,email'])
                ->orderBy('name')
                ->get()
                ->map(fn (SchoolClass $schoolClass) => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'grade_level' => $schoolClass->grade_level,
                    'major' => $schoolClass->major,
                    'academic_year' => $schoolClass->academic_year,
                    'student_count' => $schoolClass->members
                        ->where('member_role', ClassMemberRole::Student)
                        ->count(),
                ]),
            'sessions' => AttendanceSession::query()
                ->with([
                    'schoolClass.members',
                    'schoolClass:id,name',
                    'opener:id,name',
                    'records.student:id,name',
                ])
                ->latest()
                ->get()
                ->pipe(fn ($sessions) => $this->attendanceSummaryService->summarizeCollection($sessions)),
            'defaultStatus' => 'present',
            'openStatus' => SessionStatus::Open->value,
            'todayDate' => now()->toDateString(),
            'flashStatus' => session('status'),
        ];
    }
}
