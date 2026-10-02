<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassPolicy
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

    public function view(User $user, SchoolClass $schoolClass): bool
    {
        return $user->belongsToSchoolClass($schoolClass);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function assignTeacher(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    public function removeTeacher(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    public function enrollStudent(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    public function removeStudent(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    public function assignSubjectTeacher(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    public function updateSubjectTeacher(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    public function removeSubjectTeacher(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }
}
