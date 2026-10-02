import AppLayout from '@/Layouts/AppLayout';

const navigation = [
    {
        label: 'Dashboard',
        href: route('dashboard'),
        active: 'dashboard',
    },
    {
        label: 'My Classes',
        href: route('student.classes.index'),
        active: 'student.classes.*',
    },
    {
        label: 'QR Identity',
        href: route('student.qr.index'),
        active: 'student.qr.*',
    },
    {
        label: 'Materials',
        href: route('student.materials.index'),
        active: 'student.materials.*',
    },
    {
        label: 'Assignments',
        href: route('student.assignments.index'),
        active: 'student.assignments.*',
    },
];

export default function StudentLayout(props) {
    return (
        <AppLayout
            {...props}
            navigation={navigation}
            accent="amber"
            roleTitle="Siswa"
        />
    );
}
