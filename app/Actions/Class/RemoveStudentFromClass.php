<?php

namespace App\Actions\Class;

use App\Enums\ClassMemberRole;
use App\Models\SchoolClass;

class RemoveStudentFromClass
{
    public function handle(SchoolClass $schoolClass, int $userId): void
    {
        $schoolClass->members()
            ->where('user_id', $userId)
            ->where('member_role', ClassMemberRole::Student)
            ->delete();
    }
}
