<?php

namespace App\Actions\Approval;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectUser
{
    public function handle(User $user, User $actor, ?string $reason = null): void
    {
        DB::transaction(function () use ($user, $actor, $reason) {
            $user->update([
                'approval_status' => ApprovalStatus::Rejected,
                'rejected_at' => now(),
                'rejected_by' => $actor->id,
                'rejection_reason' => $reason,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            ApprovalLog::create([
                'user_id' => $user->id,
                'action' => 'reject',
                'acted_by' => $actor->id,
                'reason' => $reason,
            ]);
        });
    }
}
