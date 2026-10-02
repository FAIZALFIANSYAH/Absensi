<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approval\ApproveUser;
use App\Actions\Approval\RejectUser;
use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approval\ApproveUserRequest;
use App\Http\Requests\Approval\RejectUserRequest;
use App\Models\ApprovalLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApproveUser $approveUser,
        private readonly RejectUser $rejectUser,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('Admin/Approval/Index', [
            'users' => User::query()
                ->where('approval_status', ApprovalStatus::Pending)
                ->latest()
                ->get(['id', 'name', 'email', 'role', 'approval_status', 'created_at']),
            'recentLogs' => ApprovalLog::query()
                ->with(['user:id,name,email', 'actor:id,name'])
                ->latest('created_at')
                ->limit(8)
                ->get()
                ->map(fn (ApprovalLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'reason' => $log->reason,
                    'created_at' => $log->created_at,
                    'user' => $log->user ? [
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'actor' => $log->actor ? [
                        'name' => $log->actor->name,
                    ] : null,
                ]),
            'status' => session('status'),
        ]);
    }

    public function approve(ApproveUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('approve', $user);

        $this->approveUser->handle($user, $request->user());

        return back()->with('status', 'Akun berhasil disetujui.');
    }

    public function reject(RejectUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('reject', $user);

        $reason = $request->string('reason')->toString() ?: null;

        $this->rejectUser->handle($user, $request->user(), $reason);

        return back()->with('status', 'Akun berhasil ditolak.');
    }
}
