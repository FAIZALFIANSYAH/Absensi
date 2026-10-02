<?php

namespace App\Actions\Attendance;

use App\Enums\ApprovalStatus;
use App\Enums\ClassMemberRole;
use App\Enums\SessionStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordAttendanceScan
{
    public function handle(AttendanceSession $session, User $scanner, array $payload): AttendanceRecord
    {
        $lockKey = sprintf('attendance-scan:%s:%s', $session->id, sha1($payload['qr_token']));

        try {
            return Cache::lock($lockKey, 5)->block(2, function () use ($session, $scanner, $payload) {
                return DB::transaction(function () use ($session, $scanner, $payload) {
                    $lockedSession = AttendanceSession::query()
                        ->with('schoolClass')
                        ->lockForUpdate()
                        ->findOrFail($session->id);

                    if ($lockedSession->status !== SessionStatus::Open) {
                        throw ValidationException::withMessages([
                            'attendance_session_id' => 'Sesi absensi sudah ditutup.',
                        ]);
                    }

                    $studentProfile = StudentProfile::query()
                        ->where('qr_token', $payload['qr_token'])
                        ->with('user')
                        ->first();

                    if (! $studentProfile || ! $studentProfile->user) {
                        throw ValidationException::withMessages([
                            'qr_token' => 'QR siswa tidak valid.',
                        ]);
                    }

                    $student = $studentProfile->user;

                    if ($student->approval_status !== ApprovalStatus::Approved) {
                        throw ValidationException::withMessages([
                            'qr_token' => 'Akun siswa belum aktif.',
                        ]);
                    }

                    $isMember = $lockedSession->schoolClass
                        ->members()
                        ->where('user_id', $student->id)
                        ->where('member_role', ClassMemberRole::Student)
                        ->exists();

                    if (! $isMember) {
                        throw ValidationException::withMessages([
                            'qr_token' => 'Siswa bukan anggota kelas pada sesi ini.',
                        ]);
                    }

                    if (AttendanceRecord::query()
                        ->where('attendance_session_id', $lockedSession->id)
                        ->where('student_id', $student->id)
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'qr_token' => 'Siswa sudah melakukan scan pada sesi ini.',
                        ]);
                    }

                    try {
                        return AttendanceRecord::create([
                            'attendance_session_id' => $lockedSession->id,
                            'student_id' => $student->id,
                            'scanned_by' => $scanner->id,
                            'scanned_at' => now(),
                            'status' => $payload['status'],
                            'scan_payload' => [
                                'qr_token' => $studentProfile->qr_token,
                                'student_name' => $student->name,
                                'student_email' => $student->email,
                                'nis' => $studentProfile->nis,
                            ],
                            'notes' => $payload['notes'] ?? null,
                        ]);
                    } catch (QueryException $exception) {
                        if ($exception->getCode() !== '23000') {
                            throw $exception;
                        }

                        throw ValidationException::withMessages([
                            'qr_token' => 'Siswa sudah melakukan scan pada sesi ini.',
                        ]);
                    }
                });
            });
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'qr_token' => 'Scan untuk QR ini sedang diproses. Coba ulangi sebentar lagi.',
            ]);
        }
    }
}
