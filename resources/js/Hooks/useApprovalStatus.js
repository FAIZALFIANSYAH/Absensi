import { usePage } from '@inertiajs/react';

export default function useApprovalStatus() {
    const user = usePage().props.auth.user;
    const status = user?.approval_status ?? null;

    return {
        approvalStatus: status,
        isApproved: status === 'approved',
        isPending: status === 'pending',
        isRejected: status === 'rejected',
    };
}
