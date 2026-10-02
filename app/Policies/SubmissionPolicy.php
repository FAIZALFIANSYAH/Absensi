<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($submission->student_id === $user->id) {
            return true;
        }

        return $user->role === UserRole::Teacher
            && $user->belongsToSchoolClass($submission->assignment?->school_class_id);
    }
}
