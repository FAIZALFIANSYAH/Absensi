<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\User;

class AttendanceSessionPolicy
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

    public function create(User $user, SchoolClass $schoolClass): bool
    {
        return $user->belongsToSchoolClass($schoolClass);
    }

    public function scan(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->belongsToSchoolClass($attendanceSession->school_class_id);
    }

    public function close(User $user, AttendanceSession $attendanceSession): bool
    {
        return $this->scan($user, $attendanceSession);
    }
}
