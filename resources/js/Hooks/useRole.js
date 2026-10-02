import { usePage } from '@inertiajs/react';

export default function useRole() {
    const user = usePage().props.auth.user;

    return {
        role: user?.role ?? null,
        isAdmin: user?.role === 'superadmin',
        isTeacher: user?.role === 'teacher',
        isStudent: user?.role === 'student',
    };
}
