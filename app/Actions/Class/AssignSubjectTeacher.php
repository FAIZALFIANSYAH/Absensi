<?php

namespace App\Actions\Class;

use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;

class AssignSubjectTeacher
{
    public function __construct(
        private readonly AssignTeacherToClass $assignTeacherToClass,
    ) {
    }

    public function handle(SchoolClass $schoolClass, int $teacherId, string $subjectName): ClassSubjectTeacher
    {
        $this->assignTeacherToClass->handle($schoolClass, $teacherId);

        return ClassSubjectTeacher::updateOrCreate(
            [
                'school_class_id' => $schoolClass->id,
                'subject_name' => trim($subjectName),
            ],
            [
                'teacher_id' => $teacherId,
            ],
        );
    }
}
