import AppLayout from '@/Layouts/AppLayout';

export default function AuthenticatedLayout(props) {
    return (
        <AppLayout
            {...props}
            navigation={[
                {
                    label: 'Dashboard',
                    href: route('dashboard'),
                    active: 'dashboard',
                },
            ]}
            accent="cyan"
            roleTitle="Akun"
        />
    );
}
