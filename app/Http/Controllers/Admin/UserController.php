<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approval\ApproveUser;
use App\Actions\Approval\RejectUser;
use App\Actions\User\ActivateUser;
use App\Actions\User\CreateUser;
use App\Actions\User\DeactivateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\SendResetPasswordLink;
use App\Actions\User\UpdateUser;
use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approval\ApproveUserRequest;
use App\Http\Requests\Approval\RejectUserRequest;
use App\Http\Requests\User\SendPasswordResetLinkRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\ToggleUserActiveRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct( 
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly DeleteUser $deleteUser,
        private readonly ActivateUser $activateUser,
        private readonly DeactivateUser $deactivateUser,
        private readonly SendResetPasswordLink $sendResetPasswordLink,
        private readonly ApproveUser $approveUser,
        private readonly RejectUser $rejectUser,
    ) {
    }

    public function index(UserIndexRequest $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validated();
        $authUser = $request->user();

        $users = User::query()
            ->with(['studentProfile', 'teacherProfile'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['approval_status'] ?? null, fn ($query, $status) => $query->where('approval_status', $status))
            ->when($filters['active_status'] ?? null, function ($query, $activeStatus) {
                $query->where('is_active', $activeStatus === 'active');
            })
            ->latest()
            ->get()
            ->map(fn (User $user) => $this->mapUser($user, $authUser))
            ->values();

        return Inertia::render('Admin/Users/Index', [
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => $filters['role'] ?? '',
                'approval_status' => $filters['approval_status'] ?? '',
                'active_status' => $filters['active_status'] ?? '',
            ],
            'users' => $users,
            'options' => [
                'roles' => collect(UserRole::cases())->map(fn (UserRole $role) => [
                    'value' => $role->value,
                    'label' => ucfirst($role->value),
                ])->values(),
                'approvalStatuses' => collect(ApprovalStatus::cases())->map(fn (ApprovalStatus $status) => [
                    'value' => $status->value,
                    'label' => ucfirst($status->value),
                ])->values(),
                'genders' => [
                    ['value' => 'male', 'label' => 'Laki-laki'],
                    ['value' => 'female', 'label' => 'Perempuan'],
                ],
            ],
            'summary' => [
                'total' => $users->count(),
                'pending' => $users->where('approval_status', ApprovalStatus::Pending->value)->count(),
                'active' => $users->where('is_active', true)->count(),
                'inactive' => $users->where('is_active', false)->count(),
            ],
            'flashStatus' => session('status'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->createUser->handle($request->validated(), $request->user());

        return back()->with('status', 'User berhasil dibuat.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->updateUser->handle($user, $request->validated());

        return back()->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->deleteUser->handle($user);

        return back()->with('status', 'User berhasil dihapus.');
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

        $this->rejectUser->handle($user, $request->user(), $request->string('reason')->toString() ?: null);

        return back()->with('status', 'Akun berhasil ditolak.');
    }

    public function activate(ToggleUserActiveRequest $request, User $user): RedirectResponse
    {
        $this->authorize('activate', $user);

        $this->activateUser->handle($user);

        return back()->with('status', 'User berhasil diaktifkan kembali.');
    }

    public function deactivate(ToggleUserActiveRequest $request, User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $this->deactivateUser->handle($user, $request->user());

        return back()->with('status', 'User berhasil dinonaktifkan.');
    }

    public function sendResetLink(SendPasswordResetLinkRequest $request, User $user): RedirectResponse
    {
        $this->authorize('sendPasswordResetLink', $user);

        $this->sendResetPasswordLink->handle($user);

        return back()->with('status', 'Link reset password berhasil dikirim ke email user.');
    }

    private function mapUser(User $user, User $authUser): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'approval_status' => $user->approval_status->value,
            'is_active' => $user->is_active,
            'approved_at' => $user->approved_at?->toIso8601String(),
            'rejected_at' => $user->rejected_at?->toIso8601String(),
            'rejection_reason' => $user->rejection_reason,
            'deactivated_at' => $user->deactivated_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
            'profile' => [
                'nis' => $user->studentProfile?->nis,
                'nip' => $user->teacherProfile?->nip,
                'nik' => $user->studentProfile?->nik ?? $user->teacherProfile?->nik,
                'phone' => $user->studentProfile?->phone ?? $user->teacherProfile?->phone,
                'gender' => $user->studentProfile?->gender ?? $user->teacherProfile?->gender,
                'birth_date' => $user->studentProfile?->birth_date?->format('Y-m-d') ?? $user->teacherProfile?->birth_date?->format('Y-m-d'),
                'address' => $user->studentProfile?->address ?? $user->teacherProfile?->address,
            ],
            'can' => [
                'update' => $authUser->can('update', $user),
                'delete' => $authUser->can('delete', $user),
                'approve' => $authUser->can('approve', $user),
                'reject' => $authUser->can('reject', $user),
                'activate' => $authUser->can('activate', $user),
                'deactivate' => $authUser->can('deactivate', $user),
                'send_reset_link' => $authUser->can('sendPasswordResetLink', $user),
            ],
        ];
    }
}
