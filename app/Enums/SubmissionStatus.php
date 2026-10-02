<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case Late = 'late';
    case Graded = 'graded';
}
