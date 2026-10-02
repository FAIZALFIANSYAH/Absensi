<?php

namespace App\Actions\User;

use App\Models\User;

class DeactivateUser
{
    public function handle(User $user, User $actor): void
    {
        $user->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => $actor->id,
        ]);
    }
}
