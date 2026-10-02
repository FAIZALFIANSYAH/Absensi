<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\ClassMemberRole;
use App\Enums\SessionStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Support\Collection;

class AttendanceSummaryService
{
    public function summarizeCollection(Collection $sessions): Collection
    {
        return $sessions->map(fn (AttendanceSession $session) => $this->summarize($session));
    }

    public function summarize(AttendanceSession $session): array
    {
        $records = $session->records->map(fn (AttendanceRecord $record) => [
            'id' => $record->id,
            'student_id' => $record->student_id,
            'student_name' => $record->student?->name ?? data_get($record->scan_payload, 'student_name'),
            'student_email' => $record->student?->email ?? data_get($record->scan_payload, 'student_email'),
            'student_nis' => data_get($record->scan_payload, 'nis'),
            'status' => $record->status->value,
            'scanned_at' => $record->scanned_at?->toIso8601String(),
            'notes' => $record->notes,
        ])->values();

        $studentCount = $session->schoolClass
            ? $session->schoolClass->members->where('member_role', ClassMemberRole::Student)->count()
            : 0;

        $scannedCount = $records->count();

        return [
            'id' => $session->id,
            'school_class' => [
                'name' => $session->schoolClass?->name,
            ],
            'opener' => $session->opener?->name,
            'session_date' => $session->session_date?->format('Y-m-d'),
            'start_time' => $session->start_time?->format('H:i:s'),
            'end_time' => $session->end_time?->format('H:i:s'),
            'status' => $session->status->value,
            'can_close' => $session->status === SessionStatus::Open,
            'notes' => $session->notes,
            'student_count' => $studentCount,
            'scanned_count' => $scannedCount,
            'remaining_count' => max($studentCount - $scannedCount, 0),
            'progress_percent' => $studentCount > 0
                ? (int) round(($scannedCount / $studentCount) * 100)
                : 0,
            'status_breakdown' => [
                AttendanceStatus::Present->value => $records->where('status', AttendanceStatus::Present->value)->count(),
                AttendanceStatus::Late->value => $records->where('status', AttendanceStatus::Late->value)->count(),
                AttendanceStatus::Excused->value => $records->where('status', AttendanceStatus::Excused->value)->count(),
                AttendanceStatus::Absent->value => $records->where('status', AttendanceStatus::Absent->value)->count(),
            ],
            'records' => $records,
        ];
    }
}
