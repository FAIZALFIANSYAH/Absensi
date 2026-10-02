<?php

namespace App\Actions\Attendance;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CloseAttendanceSession
{
    public function handle(AttendanceSession $session): void
    {
        DB::transaction(function () use ($session) {
            $lockedSession = AttendanceSession::query()
                ->lockForUpdate()
                ->findOrFail($session->id);

            if ($lockedSession->status !== SessionStatus::Open) {
                throw ValidationException::withMessages([
                    'attendance_session_id' => 'Sesi absensi sudah ditutup.',
                ]);
            }

            $lockedSession->update([
                'status' => SessionStatus::Closed,
                'end_time' => now()->format('H:i:s'),
            ]);
        });
    }
}
