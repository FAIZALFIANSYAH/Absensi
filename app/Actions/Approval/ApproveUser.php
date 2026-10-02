<?php

namespace App\Actions\Approval;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalLog;
use App\Models\User;
use App\Services\QRCode\StudentQrIdentityService;
use Illuminate\Support\Facades\DB;

class ApproveUser
{
    public function __construct(
        private readonly StudentQrIdentityService $studentQrIdentityService,
    ) {
    }

    public function handle(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $user->update([
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'approved_by' => $actor->id,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ]);

            $this->studentQrIdentityService->ensureForUser($user);

            ApprovalLog::create([
                'user_id' => $user->id,
                'action' => 'approve',
                'acted_by' => $actor->id,
            ]);
        });
    }
}
