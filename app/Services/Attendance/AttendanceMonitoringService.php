<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\ClassMemberRole;
use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class AttendanceMonitoringService
{
    public function buildForClasses(EloquentCollection $classes, array $filters): array
    {
        $classIds = $classes->pluck('id')->all();
        $todayDate = now()->toDateString();
        $selectedClassId = $filters['school_class_id'] ?? '';
        $selectedDate = $filters['session_date'] ?? $todayDate;
        $selectedStatus = $filters['status'] ?? 'all';

        $sessions = AttendanceSession::query()
            ->whereIn('school_class_id', $classIds)
            ->when($selectedClassId !== '', fn ($query) => $query->where('school_class_id', $selectedClassId))
            ->when($selectedDate, fn ($query) => $query->whereDate('session_date', $selectedDate))
            ->when($selectedStatus !== 'all', fn ($query) => $query->where('status', $selectedStatus))
            ->with([
                'schoolClass.members.user.studentProfile',
                'schoolClass:id,name,grade_level,major,academic_year',
                'opener:id,name',
                'records.student.studentProfile',
            ])
            ->orderByDesc('session_date')
            ->latest()
            ->get();

        $sessionCards = $sessions->map(fn (AttendanceSession $session) => $this->buildSessionCard($session))->values();

        $activeSessionCards = $sessionCards->where('status', SessionStatus::Open->value)->values();

        return [
            'filters' => [
                'school_class_id' => $selectedClassId === '' ? '' : (string) $selectedClassId,
                'session_date' => $selectedDate,
                'status' => $selectedStatus,
            ],
            'filter_options' => [
                'classes' => $classes->map(fn (SchoolClass $schoolClass) => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'grade_level' => $schoolClass->grade_level,
                    'major' => $schoolClass->major,
                    'academic_year' => $schoolClass->academic_year,
                ])->values(),
                'statuses' => [
                    ['value' => 'all', 'label' => 'Semua sesi'],
                    ['value' => SessionStatus::Open->value, 'label' => 'Sesi terbuka'],
                    ['value' => SessionStatus::Closed->value, 'label' => 'Sesi tertutup'],
                ],
            ],
            'overview' => [
                'today_date' => $todayDate,
                'session_count' => $sessionCards->count(),
                'open_session_count' => $activeSessionCards->count(),
                'tracked_student_count' => $activeSessionCards->sum('student_count'),
                'scanned_student_count' => $activeSessionCards->sum('scanned_count'),
                'pending_student_count' => $activeSessionCards->sum('pending_count'),
                'has_open_session' => $activeSessionCards->isNotEmpty(),
                'poll_interval_seconds' => 15,
            ],
            'sessions' => $sessionCards,
        ];
    }

    private function buildSessionCard(AttendanceSession $session): array
    {
        $studentMembers = $session->schoolClass?->members
            ->where('member_role', ClassMemberRole::Student)
            ->values() ?? collect();

        $records = $session->records
            ->sortBy('scanned_at')
            ->values();

        $recordByStudentId = $records->keyBy('student_id');

        $presentStudents = $records->map(function ($record) {
            return [
                'id' => $record->student_id,
                'name' => $record->student?->name,
                'nis' => $record->student?->studentProfile?->nis,
                'status' => $record->status->value,
                'status_label' => $this->attendanceLabel($record->status),
                'scanned_at' => $record->scanned_at?->toIso8601String(),
                'notes' => $record->notes,
            ];
        })->values();

        $pendingStudents = $studentMembers->filter(function ($member) use ($recordByStudentId) {
            return ! $recordByStudentId->has($member->user_id);
        })->map(function ($member) {
            return [
                'id' => $member->user_id,
                'name' => $member->user?->name,
                'nis' => $member->user?->studentProfile?->nis,
            ];
        })->values();

        $studentCount = $studentMembers->count();
        $scannedCount = $presentStudents->count();

        return [
            'id' => $session->id,
            'status' => $session->status->value,
            'status_label' => $session->status === SessionStatus::Open ? 'Terbuka' : 'Ditutup',
            'session_date' => $session->session_date?->format('Y-m-d'),
            'start_time' => $session->start_time?->format('H:i:s'),
            'end_time' => $session->end_time?->format('H:i:s'),
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
            'scanned_count' => $scannedCount,
            'pending_count' => $pendingStudents->count(),
            'progress_percent' => $studentCount > 0
                ? (int) round(($scannedCount / $studentCount) * 100)
                : 0,
            'status_breakdown' => [
                AttendanceStatus::Present->value => $presentStudents->where('status', AttendanceStatus::Present->value)->count(),
                AttendanceStatus::Late->value => $presentStudents->where('status', AttendanceStatus::Late->value)->count(),
                AttendanceStatus::Excused->value => $presentStudents->where('status', AttendanceStatus::Excused->value)->count(),
                AttendanceStatus::Absent->value => $presentStudents->where('status', AttendanceStatus::Absent->value)->count(),
            ],
            'present_students' => $presentStudents,
            'pending_students' => $pendingStudents,
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
