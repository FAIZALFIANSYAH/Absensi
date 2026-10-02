<?php

namespace App\Actions\Class;

use App\Enums\ClassMemberRole;
use App\Models\ClassMember;
use App\Models\SchoolClass;

class EnrollStudentToClass
{
    public function handle(SchoolClass $schoolClass, int $userId): ClassMember
    {
        return ClassMember::firstOrCreate([
            'school_class_id' => $schoolClass->id,
            'user_id' => $userId,
            'member_role' => ClassMemberRole::Student,
        ], [
            'joined_at' => now(),
        ]);
    }
}
