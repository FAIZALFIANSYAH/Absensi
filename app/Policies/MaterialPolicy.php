<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\User;

class MaterialPolicy
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

    public function view(User $user, Material $material): bool
    {
        return $user->belongsToSchoolClass($material->school_class_id);
    }

    public function create(User $user, SchoolClass $schoolClass): bool
    {
        return $user->belongsToSchoolClass($schoolClass);
    }
}
