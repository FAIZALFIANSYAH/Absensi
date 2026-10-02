import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }) {
    const features = [
        {
            title: 'Registrasi Terpisah',
            description:
                'Siswa dan guru mendaftar lewat jalur yang berbeda agar data dan approval lebih rapi sejak awal.',
        },
        {
            title: 'Approval Manual',
            description:
                'Setiap akun baru masuk ke status pending dan hanya akun approved yang bisa mengakses fitur utama.',
        },
        {
            title: 'Dashboard Berbasis Role',
            description:
                'Superadmin, guru, dan siswa diarahkan ke area kerja masing-masing setelah login.',
        },
    ];

    return (
        <>
            <Head title="Absensi QR & Smart Class" />

            <div className="min-h-screen bg-slate-950 text-slate-100">
                <div className="mx-auto max-w-7xl px-6 py-10 lg:px-8">
                    <header className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.32em] text-cyan-300">
                                Absensi QR & Smart Class
                            </p>
                            <h1 className="mt-3 max-w-3xl text-4xl font-semibold leading-tight text-white sm:text-5xl">
                                Fondasi autentikasi dan approval akun sekolah sudah siap dipakai.
                            </h1>
                            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                                Sistem ini memisahkan registrasi siswa dan guru,
                                menahan akun baru pada status pending, lalu
                                mengarahkan setiap user approved ke dashboard yang
                                sesuai dengan rolenya.
                            </p>
                        </div>

                        <nav className="flex flex-wrap gap-3">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-full bg-cyan-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                                >
                                    Buka Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="rounded-full border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:border-cyan-300 hover:text-cyan-200"
                                    >
                                        Masuk
                                    </Link>
                                    <Link
                                        href={route('register.student')}
                                        className="rounded-full bg-cyan-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                                    >
                                        Daftar Siswa
                                    </Link>
                                    <Link
                                        href={route('register.teacher')}
                                        className="rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-200"
                                    >
                                        Daftar Guru
                                    </Link>
                                </>
                            )}
                        </nav>
                    </header>

                    <main className="mt-16 grid gap-6 lg:grid-cols-[1.4fr_0.9fr]">
                        <section className="grid gap-6 sm:grid-cols-3">
                            {features.map((feature) => (
                                <article
                                    key={feature.title}
                                    className="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
                                >
                                    <p className="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-300">
                                        Phase 1
                                    </p>
                                    <h2 className="mt-3 text-xl font-semibold text-white">
                                        {feature.title}
                                    </h2>
                                    <p className="mt-3 text-sm leading-7 text-slate-300">
                                        {feature.description}
                                    </p>
                                </article>
                            ))}
                        </section>

                        <section className="rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-400/15 via-slate-900 to-slate-900 p-7">
                            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-300">
                                Alur Utama
                            </p>
                            <div className="mt-6 space-y-5">
                                <div>
                                    <h3 className="text-lg font-semibold text-white">
                                        1. Registrasi akun
                                    </h3>
                                    <p className="mt-1 text-sm leading-6 text-slate-300">
                                        Siswa dan guru mendaftar lewat formulir yang terpisah.
                                    </p>
                                </div>
                                <div>
                                    <h3 className="text-lg font-semibold text-white">
                                        2. Review superadmin
                                    </h3>
                                    <p className="mt-1 text-sm leading-6 text-slate-300">
                                        Akun baru masuk ke status pending dan ditinjau dari area approval admin.
                                    </p>
                                </div>
                                <div>
                                    <h3 className="text-lg font-semibold text-white">
                                        3. Akses dashboard
                                    </h3>
                                    <p className="mt-1 text-sm leading-6 text-slate-300">
                                        Setelah approved, user diarahkan otomatis ke dashboard sesuai role.
                                    </p>
                                </div>
                            </div>
                        </section>
                    </main>

                    <section className="mt-16 rounded-3xl border border-white/10 bg-white/5 p-6 sm:p-8">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 className="text-2xl font-semibold text-white">
                                    Siap menutup Phase 1 dan lanjut ke domain inti
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-7 text-slate-300">
                                    Fondasi auth, approval, dan pemisahan role sudah menjadi dasar
                                    untuk Phase 2: profil siswa dan guru serta QR identity flow.
                                </p>
                            </div>
                            {!auth.user && (
                                <Link
                                    href={route('register.student')}
                                    className="inline-flex rounded-full border border-cyan-300 px-5 py-3 text-sm font-semibold text-cyan-200 transition hover:bg-cyan-300/10"
                                >
                                    Mulai dari registrasi siswa
                                </Link>
                            )}
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
