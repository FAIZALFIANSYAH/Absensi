import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

export default function PendingApproval() {
    const user = usePage().props.auth.user;
    const roleLabel = {
        teacher: 'guru',
        student: 'siswa',
    }[user?.role] ?? 'pengguna';

    return (
        <GuestLayout>
            <Head title="Menunggu Persetujuan" />

            <div className="space-y-6">
                <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                    Status pending approval
                </div>

                <div className="space-y-3">
                    <h1 className="text-2xl font-semibold text-slate-900">
                        Akun Menunggu Persetujuan
                    </h1>
                    <p className="text-sm leading-6 text-slate-600">
                        Akun {roleLabel} untuk{' '}
                        <span className="font-medium text-slate-800">
                            {user?.email}
                        </span>{' '}
                        sudah terdaftar, tetapi akses ke dashboard utama masih
                        ditahan sampai disetujui oleh admin sekolah.
                    </p>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                    <p className="font-medium text-slate-800">
                        Yang bisa Anda lakukan sekarang
                    </p>
                    <ul className="mt-2 space-y-1">
                        <li>Menunggu proses review dari superadmin.</li>
                        <li>Memastikan email yang didaftarkan sudah benar.</li>
                        <li>Masuk kembali setelah akun disetujui.</li>
                    </ul>
                </div>

                <div className="flex flex-wrap gap-3 pt-2">
                    <PrimaryButton onClick={() => router.post(route('logout'))}>
                        Keluar
                    </PrimaryButton>
                    <Link href={route('login')}>
                        <SecondaryButton>Layar Login</SecondaryButton>
                    </Link>
                </div>
            </div>
        </GuestLayout>
    );
}
