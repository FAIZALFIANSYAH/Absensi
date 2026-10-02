import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';

export default function Register() {
    return (
        <GuestLayout>
            <Head title="Pilih Jenis Registrasi" />

            <div className="space-y-6">
                <div className="space-y-2">
                    <h1 className="text-2xl font-semibold text-slate-900">
                        Pilih jenis akun
                    </h1>
                    <p className="text-sm leading-6 text-slate-600">
                        Registrasi dipisahkan antara siswa dan guru agar proses
                        approval admin dan pengembangan profil berikutnya tetap rapi.
                    </p>
                </div>

                <div className="grid gap-3">
                    <Link
                        href={route('register.student')}
                        className="rounded-2xl border border-slate-200 px-4 py-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-slate-900"
                    >
                        <span className="block text-base font-semibold text-slate-900">
                            Registrasi Siswa
                        </span>
                        <span className="mt-1 block">
                            Untuk akun siswa yang nanti akan memiliki identitas QR pribadi.
                        </span>
                    </Link>

                    <Link
                        href={route('register.teacher')}
                        className="rounded-2xl border border-slate-200 px-4 py-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-slate-900"
                    >
                        <span className="block text-base font-semibold text-slate-900">
                            Registrasi Guru
                        </span>
                        <span className="mt-1 block">
                            Untuk akun guru yang akan mengelola kelas dan absensi.
                        </span>
                    </Link>
                </div>

                <div className="text-sm text-slate-600">
                    Sudah punya akun?{' '}
                    <Link href={route('login')} className="font-medium underline">
                        Masuk di sini
                    </Link>
                </div>
            </div>
        </GuestLayout>
    );
}
