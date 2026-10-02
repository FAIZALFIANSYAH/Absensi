<?php

namespace App\Actions\Class;

use App\Models\ClassSubjectTeacher;

class RemoveSubjectTeacher
{
    public function handle(ClassSubjectTeacher $subjectTeacher): void
    {
        $subjectTeacher->delete();
    }
}
