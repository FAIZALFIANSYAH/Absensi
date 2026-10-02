<?php

namespace App\Enums;

enum ClassMemberRole: string
{
    case Teacher = 'teacher';
    case Student = 'student';
    case HomeroomTeacher = 'homeroom_teacher';
}
