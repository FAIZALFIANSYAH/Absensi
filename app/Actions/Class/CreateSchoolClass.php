<?php

namespace App\Actions\Class;

use App\Enums\ClassMemberRole;
use App\Models\ClassMember;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateSchoolClass
{
    public function handle(array $payload, User $actor): SchoolClass
    {
        return DB::transaction(function () use ($payload, $actor) {
            $schoolClass = SchoolClass::create([
                'name' => $payload['name'],
                'slug' => Str::slug($payload['name'].'-'.$payload['academic_year']),
                'grade_level' => $payload['grade_level'],
                'major' => $payload['major'] ?? null,
                'academic_year' => $payload['academic_year'],
                'homeroom_teacher_id' => $payload['homeroom_teacher_id'] ?? null,
                'created_by' => $actor->id,
            ]);

            if ($schoolClass->homeroom_teacher_id) {
                ClassMember::firstOrCreate([
                    'school_class_id' => $schoolClass->id,
                    'user_id' => $schoolClass->homeroom_teacher_id,
                    'member_role' => ClassMemberRole::HomeroomTeacher,
                ], [
                    'joined_at' => now(),
                ]);
            }

            return $schoolClass;
        });
    }
}
