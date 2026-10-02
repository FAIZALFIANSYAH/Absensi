<?php

namespace App\Actions\Class;

use App\Enums\ClassMemberRole;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class RemoveTeacherFromClass
{
    public function handle(SchoolClass $schoolClass, int $userId): void
    {
        DB::transaction(function () use ($schoolClass, $userId) {
            if ($schoolClass->homeroom_teacher_id === $userId) {
                $schoolClass->forceFill([
                    'homeroom_teacher_id' => null,
                ])->save();
            }

            $schoolClass->members()
                ->where('user_id', $userId)
                ->whereIn('member_role', [
                    ClassMemberRole::Teacher,
                    ClassMemberRole::HomeroomTeacher,
                ])
                ->delete();
        });
    }
}
