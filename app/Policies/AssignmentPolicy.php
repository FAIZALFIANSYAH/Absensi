<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\User;

class AssignmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isApproved();
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->belongsToSchoolClass($assignment->school_class_id);
    }

    public function create(User $user, SchoolClass $schoolClass): bool
    {
        return $user->belongsToSchoolClass($schoolClass);
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        return $user->belongsToSchoolClass($assignment->school_class_id);
    }
}
