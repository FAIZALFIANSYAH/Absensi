<?php

namespace App\Actions\Assignment;

use App\Enums\AssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Support\SafeUploadedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitAssignment
{
    public function handle(Assignment $assignment, User $student, array $payload): Submission
    {
        if ($assignment->status !== AssignmentStatus::Published) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Tugas ini tidak sedang dibuka untuk pengumpulan.',
            ]);
        }

        $isMember = $assignment->schoolClass
            ->members()
            ->where('user_id', $student->id)
            ->where('member_role', 'student')
            ->exists();

        if (! $isMember) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Anda bukan anggota kelas untuk tugas ini.',
            ]);
        }

        if (Submission::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Tugas ini sudah pernah Anda kumpulkan.',
            ]);
        }

        /** @var UploadedFile $file */
        $file = $payload['file'];

        return DB::transaction(function () use ($assignment, $student, $payload, $file) {
            $filePath = $file->store("submissions/{$assignment->id}", 'local');
            $submittedAt = now();

            return Submission::create([
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'file_path' => $filePath,
                'file_name' => SafeUploadedFile::originalName($file),
                'notes' => $payload['notes'] ?? null,
                'submitted_at' => $submittedAt,
                'status' => $submittedAt->gt($assignment->deadline_at)
                    ? SubmissionStatus::Late
                    : SubmissionStatus::Submitted,
            ]);
        });
    }
}
