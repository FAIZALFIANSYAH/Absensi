import AppLayout from '@/Layouts/AppLayout';

const navigation = [
    {
        label: 'Dashboard',
        href: route('dashboard'),
        active: 'dashboard',
    },
    {
        label: 'Users',
        href: route('admin.users.index'),
        active: 'admin.users.*',
    },
    {
        label: 'Approval',
        href: route('admin.approval.index'),
        active: 'admin.approval.*',
    },
    {
        label: 'Classes',
        href: route('admin.classes.index'),
        active: 'admin.classes.*',
    },
    {
        label: 'Attendance',
        href: route('admin.attendance.index'),
        active: 'admin.attendance.*',
    },
    {
        label: 'Materials',
        href: route('admin.materials.index'),
        active: 'admin.materials.*',
    },
    {
        label: 'Assignments',
        href: route('admin.assignments.index'),
        active: 'admin.assignments.*',
    },
    {
        label: 'Reports',
        href: route('admin.reports.index'),
        active: 'admin.reports.*',
    },
];

export default function AdminLayout(props) {
    return (
        <AppLayout
            {...props}
            navigation={navigation}
            accent="cyan"
            roleTitle="Superadmin"
        />
    );
}
