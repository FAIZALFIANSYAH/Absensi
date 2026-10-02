import AppLayout from '@/Layouts/AppLayout';

const navigation = [
    {
        label: 'Dashboard',
        href: route('dashboard'),
        active: 'dashboard',
    },
    {
        label: 'Classes',
        href: route('teacher.classes.index'),
        active: 'teacher.classes.*',
    },
    {
        label: 'Attendance',
        href: route('teacher.attendance.index'),
        active: 'teacher.attendance.*',
    },
    {
        label: 'Materials',
        href: route('teacher.materials.index'),
        active: 'teacher.materials.*',
    },
    {
        label: 'Assignments',
        href: route('teacher.assignments.index'),
        active: 'teacher.assignments.*',
    },
];

export default function TeacherLayout(props) {
    return (
        <AppLayout
            {...props}
            navigation={navigation}
            accent="emerald"
            roleTitle="Guru"
        />
    );
}
