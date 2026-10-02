<?php

namespace App\Actions\Class;

use App\Enums\ClassMemberRole;
use App\Models\ClassMember;
use App\Models\SchoolClass;

class AssignTeacherToClass
{
    public function handle(SchoolClass $schoolClass, int $userId): ClassMember
    {
        return ClassMember::firstOrCreate([
            'school_class_id' => $schoolClass->id,
            'user_id' => $userId,
            'member_role' => ClassMemberRole::Teacher,
        ], [
            'joined_at' => now(),
        ]);
    }
}
