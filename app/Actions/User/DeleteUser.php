<?php

namespace App\Actions\User;

use App\Models\ApprovalLog;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Assignment;
use App\Models\ClassMember;
use App\Models\ClassSubjectTeacher;
use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteUser
{
    public function handle(User $user): void
    {
        if ($this->hasOperationalRelations($user)) {
            throw ValidationException::withMessages([
                'user' => 'User ini tidak bisa dihapus karena sudah terhubung ke data operasional.',
            ]);
        }

        DB::transaction(function () use ($user) {
            $user->studentProfile()?->delete();
            $user->teacherProfile()?->delete();
            $user->delete();
        });
    }

    private function hasOperationalRelations(User $user): bool
    {
        return AttendanceRecord::query()->where('student_id', $user->id)->exists()
            || AttendanceRecord::query()->where('scanned_by', $user->id)->exists()
            || AttendanceSession::query()->where('opened_by', $user->id)->exists()
            || Assignment::query()->where('teacher_id', $user->id)->exists()
            || Material::query()->where('teacher_id', $user->id)->exists()
            || Submission::query()->where('student_id', $user->id)->exists()
            || Submission::query()->where('graded_by', $user->id)->exists()
            || ClassSubjectTeacher::query()->where('teacher_id', $user->id)->exists()
            || ApprovalLog::query()->where('user_id', $user->id)->exists()
            || ApprovalLog::query()->where('acted_by', $user->id)->exists()
            || ClassMember::query()->where('user_id', $user->id)->exists()
            || SchoolClass::query()->where('homeroom_teacher_id', $user->id)->exists()
            || SchoolClass::query()->where('created_by', $user->id)->exists()
            || User::query()->where('approved_by', $user->id)->exists()
            || User::query()->where('rejected_by', $user->id)->exists()
            || User::query()->where('deactivated_by', $user->id)->exists();
    }
}
