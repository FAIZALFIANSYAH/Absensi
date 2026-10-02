<?php

namespace App\Services\Reports;

use App\Enums\AttendanceStatus;
use App\Enums\ClassMemberRole;
use App\Enums\SessionStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassMember;
use App\Models\SchoolClass;

class AttendanceReportService
{
    public function build(array $filters): array
    {
        $selectedClassId = $filters['school_class_id'] ?? '';
        $selectedDate = $filters['session_date'] ?? now()->toDateString();
        $selectedSessionStatus = $filters['session_status'] ?? 'all';

        $classes = SchoolClass::query()
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level', 'major', 'academic_year']);

        $sessions = AttendanceSession::query()
            ->when($selectedClassId !== '', fn ($query) => $query->where('school_class_id', $selectedClassId))
            ->when($selectedDate, fn ($query) => $query->whereDate('session_date', $selectedDate))
            ->when($selectedSessionStatus !== 'all', fn ($query) => $query->where('status', $selectedSessionStatus))
            ->with([
                'schoolClass.members.user.studentProfile',
                'schoolClass:id,name,grade_level,major,academic_year',
                'opener:id,name',
                'records.student.studentProfile',
                'records.scanner:id,name',
            ])
            ->orderByDesc('session_date')
            ->latest('start_time')
            ->get();

        $reportSessions = $sessions->map(fn (AttendanceSession $session) => $this->buildSessionReport($session))->values();

        return [
            'filters' => [
                'school_class_id' => $selectedClassId === '' ? '' : (string) $selectedClassId,
                'session_date' => $selectedDate,
                'session_status' => $selectedSessionStatus,
            ],
            'filter_options' => [
                'classes' => $classes->map(fn (SchoolClass $schoolClass) => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'grade_level' => $schoolClass->grade_level,
                    'major' => $schoolClass->major,
                    'academic_year' => $schoolClass->academic_year,
                ])->values(),
                'session_statuses' => [
                    ['value' => 'all', 'label' => 'Semua sesi'],
                    ['value' => SessionStatus::Open->value, 'label' => 'Sesi terbuka'],
                    ['value' => SessionStatus::Closed->value, 'label' => 'Sesi tertutup'],
                ],
            ],
            'overview' => [
                'session_count' => $reportSessions->count(),
                'student_count' => $reportSessions->sum('student_count'),
                'recorded_count' => $reportSessions->sum('recorded_count'),
                'unrecorded_count' => $reportSessions->sum('unrecorded_count'),
                'present_count' => $reportSessions->sum('status_breakdown.present'),
                'late_count' => $reportSessions->sum('status_breakdown.late'),
                'excused_count' => $reportSessions->sum('status_breakdown.excused'),
                'absent_count' => $reportSessions->sum('status_breakdown.absent'),
            ],
            'sessions' => $reportSessions,
        ];
    }

    private function buildSessionReport(AttendanceSession $session): array
    {
        $studentMembers = $session->schoolClass?->members
            ->where('member_role', ClassMemberRole::Student)
            ->values() ?? collect();

        $records = $session->records->keyBy('student_id');

        $studentRows = $studentMembers->map(function (ClassMember $member) use ($records, $session) {
            /** @var AttendanceRecord|null $record */
            $record = $records->get($member->user_id);

            if ($record) {
                return [
                    'id' => $member->user_id,
                    'name' => $member->user?->name,
                    'nis' => $member->user?->studentProfile?->nis,
                    'status' => $record->status->value,
                    'status_label' => $this->attendanceLabel($record->status),
                    'scanned_at' => $record->scanned_at?->toIso8601String(),
                    'notes' => $record->notes,
                    'scanner_name' => $record->scanner?->name,
                    'is_recorded' => true,
                ];
            }

            return [
                'id' => $member->user_id,
                'name' => $member->user?->name,
                'nis' => $member->user?->studentProfile?->nis,
                'status' => 'unrecorded',
                'status_label' => $session->status === SessionStatus::Closed ? 'Belum tercatat' : 'Belum scan',
                'scanned_at' => null,
                'notes' => null,
                'scanner_name' => null,
                'is_recorded' => false,
            ];
        })->values();

        $studentCount = $studentRows->count();
        $recordedCount = $studentRows->where('is_recorded', true)->count();

        return [
            'id' => $session->id,
            'session_date' => $session->session_date?->format('Y-m-d'),
            'start_time' => $session->start_time?->format('H:i:s'),
            'end_time' => $session->end_time?->format('H:i:s'),
            'status' => $session->status->value,
            'status_label' => $session->status === SessionStatus::Open ? 'Terbuka' : 'Ditutup',
            'notes' => $session->notes,
            'opener' => $session->opener?->name,
            'school_class' => [
                'id' => $session->schoolClass?->id,
                'name' => $session->schoolClass?->name,
                'grade_level' => $session->schoolClass?->grade_level,
                'major' => $session->schoolClass?->major,
                'academic_year' => $session->schoolClass?->academic_year,
            ],
            'student_count' => $studentCount,
            'recorded_count' => $recordedCount,
            'unrecorded_count' => max($studentCount - $recordedCount, 0),
            'progress_percent' => $studentCount > 0
                ? (int) round(($recordedCount / $studentCount) * 100)
                : 0,
            'status_breakdown' => [
                AttendanceStatus::Present->value => $studentRows->where('status', AttendanceStatus::Present->value)->count(),
                AttendanceStatus::Late->value => $studentRows->where('status', AttendanceStatus::Late->value)->count(),
                AttendanceStatus::Excused->value => $studentRows->where('status', AttendanceStatus::Excused->value)->count(),
                AttendanceStatus::Absent->value => $studentRows->where('status', AttendanceStatus::Absent->value)->count(),
                'unrecorded' => $studentRows->where('status', 'unrecorded')->count(),
            ],
            'students' => $studentRows,
        ];
    }

    private function attendanceLabel(AttendanceStatus $status): string
    {
        return match ($status) {
            AttendanceStatus::Present => 'Hadir',
            AttendanceStatus::Late => 'Terlambat',
            AttendanceStatus::Excused => 'Izin',
            AttendanceStatus::Absent => 'Absen',
        };
    }
}
