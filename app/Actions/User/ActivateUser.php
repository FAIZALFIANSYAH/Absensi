<?php

namespace App\Actions\User;

use App\Models\User;

class ActivateUser
{
    public function handle(User $user): void
    {
        $user->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
        ]);
    }
}
