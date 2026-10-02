import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-950 px-4 py-8 text-slate-100 sm:px-6 lg:px-8">
            <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col justify-center gap-8 lg:flex-row lg:items-center lg:gap-16">
                <div className="max-w-xl space-y-5">
                    <Link href="/" className="inline-flex items-center gap-3">
                        <ApplicationLogo className="h-14 w-14 fill-current text-cyan-300" />
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.32em] text-cyan-300">
                                Absensi QR
                            </p>
                            <p className="text-lg font-semibold text-white">
                                Smart Class SMA
                            </p>
                        </div>
                    </Link>

                    <div className="space-y-3">
                        <h1 className="text-3xl font-semibold leading-tight text-white sm:text-4xl">
                            Fondasi akun sekolah yang rapi, terpisah, dan aman.
                        </h1>
                        <p className="text-sm leading-7 text-slate-300 sm:text-base">
                            Registrasi siswa dan guru dipisah, setiap akun masuk
                            ke status pending, lalu hanya user yang disetujui
                            admin yang bisa mengakses dashboard utama.
                        </p>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-3">
                        <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p className="text-xs uppercase tracking-[0.24em] text-slate-400">
                                Role
                            </p>
                            <p className="mt-2 text-sm font-medium text-white">
                                Superadmin, Guru, Siswa
                            </p>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p className="text-xs uppercase tracking-[0.24em] text-slate-400">
                                Approval
                            </p>
                            <p className="mt-2 text-sm font-medium text-white">
                                Pending, Approved, Rejected
                            </p>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p className="text-xs uppercase tracking-[0.24em] text-slate-400">
                                Akses
                            </p>
                            <p className="mt-2 text-sm font-medium text-white">
                                Dashboard berbasis role
                            </p>
                        </div>
                    </div>
                </div>

                <div className="w-full max-w-md overflow-hidden rounded-3xl border border-white/10 bg-white px-6 py-6 text-slate-900 shadow-2xl shadow-cyan-950/20 sm:px-8 [color-scheme:light]">
                    {children}
                </div>
            </div>
        </div>
    );
}
