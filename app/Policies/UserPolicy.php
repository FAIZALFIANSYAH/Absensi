<?php

namespace App\Policies;

use App\Enums\ApprovalStatus;
use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin() && $ability === 'viewAny') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function approve(User $user, User $target): bool
    {
        return $user->isAdmin()
            && $target->approval_status === ApprovalStatus::Pending
            && ! $target->isAdmin()
            && $target->id !== $user->id;
    }

    public function reject(User $user, User $target): bool
    {
        return $user->isAdmin()
            && $target->approval_status === ApprovalStatus::Pending
            && ! $target->isAdmin()
            && $target->id !== $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin() && ! $target->isAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin()
            && ! $target->isAdmin()
            && $target->id !== $user->id;
    }

    public function activate(User $user, User $target): bool
    {
        return $user->isAdmin()
            && ! $target->isAdmin()
            && $target->id !== $user->id
            && ! $target->isActive();
    }

    public function deactivate(User $user, User $target): bool
    {
        return $user->isAdmin()
            && ! $target->isAdmin()
            && $target->id !== $user->id
            && $target->isActive();
    }

    public function sendPasswordResetLink(User $user, User $target): bool
    {
        return $user->isAdmin()
            && ! $target->isAdmin()
            && $target->id !== $user->id;
    }
}
