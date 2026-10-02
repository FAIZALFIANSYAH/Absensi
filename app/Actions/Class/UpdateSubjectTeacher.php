<?php

namespace App\Actions\Class;

use App\Models\ClassSubjectTeacher;

class UpdateSubjectTeacher
{
    public function __construct(
        private readonly AssignTeacherToClass $assignTeacherToClass,
    ) {
    }

    public function handle(ClassSubjectTeacher $subjectTeacher, int $teacherId, string $subjectName): ClassSubjectTeacher
    {
        $this->assignTeacherToClass->handle($subjectTeacher->schoolClass, $teacherId);

        $subjectTeacher->update([
            'teacher_id' => $teacherId,
            'subject_name' => trim($subjectName),
        ]);

        return $subjectTeacher->refresh();
    }
}
