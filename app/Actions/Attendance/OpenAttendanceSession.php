<?php

namespace App\Actions\Attendance;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpenAttendanceSession
{
    public function handle(SchoolClass $schoolClass, User $actor, array $payload): AttendanceSession
    {
        $lockKey = sprintf('attendance-session:open:%s:%s', $schoolClass->id, $payload['session_date']);

        try {
            return Cache::lock($lockKey, 10)->block(3, function () use ($schoolClass, $actor, $payload) {
                return DB::transaction(function () use ($schoolClass, $actor, $payload) {
                    $alreadyOpen = AttendanceSession::query()
                        ->where('school_class_id', $schoolClass->id)
                        ->where('session_date', $payload['session_date'])
                        ->where('status', SessionStatus::Open)
                        ->exists();

                    if ($alreadyOpen) {
                        throw ValidationException::withMessages([
                            'school_class_id' => 'Masih ada sesi absensi terbuka untuk kelas dan tanggal ini.',
                        ]);
                    }

                    return AttendanceSession::create([
                        'school_class_id' => $schoolClass->id,
                        'opened_by' => $actor->id,
                        'session_date' => $payload['session_date'],
                        'start_time' => $payload['start_time'],
                        'end_time' => null,
                        'status' => SessionStatus::Open,
                        'notes' => $payload['notes'] ?? null,
                    ]);
                });
            });
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'school_class_id' => 'Pembukaan sesi untuk kelas ini sedang diproses. Coba beberapa detik lagi.',
            ]);
        }
    }
}
