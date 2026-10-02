import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import AttendanceMonitor from '@/Components/Attendance/AttendanceMonitor';

export default function AdminDashboard({ stats, monitoring }) {
    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Dashboard Admin
                    </h2>
                    <p className="text-sm text-slate-600">
                        Pusat kontrol approval, ringkasan sistem, dan monitoring kehadiran lintas kelas.
                    </p>
                </div>
            }
        >
            <Head title="Dashboard Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-4 md:grid-cols-4">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Menunggu approval</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {stats.pendingUsers}
                            </p>
                        </div>
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">User approved</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {stats.approvedUsers}
                            </p>
                        </div>
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Guru</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {stats.teachers}
                            </p>
                        </div>
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Siswa</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {stats.students}
                            </p>
                        </div>
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Kelas</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {stats.classes}
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Langkah berikutnya
                            </h3>
                            <p className="mt-2 text-sm leading-7 text-slate-600">
                                Phase 1 sudah menyiapkan alur approval akun, audit log dasar,
                                dan pengalihan dashboard berbasis role. Area ini menjadi gerbang
                                operasional sebelum kita masuk ke profil identitas dan QR siswa.
                            </p>
                            <div className="mt-4">
                                <Link
                                    href={route('admin.approval.index')}
                                    className="inline-flex rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                >
                                    Buka Approval User
                                </Link>
                                <Link
                                    href={route('admin.classes.index')}
                                    className="ml-3 inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                                >
                                    Kelola Kelas
                                </Link>
                                <Link
                                    href={route('admin.reports.index')}
                                    className="ml-3 inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                                >
                                    Laporan Kehadiran
                                </Link>
                            </div>
                        </div>

                        <div className="rounded-2xl bg-slate-900 p-6 text-slate-100 shadow-sm">
                            <h3 className="text-lg font-semibold text-white">
                                Checklist fondasi
                            </h3>
                            <div className="mt-4 space-y-3 text-sm leading-6 text-slate-300">
                                <p>Registrasi siswa dan guru dipisah.</p>
                                <p>User baru masuk ke pending.</p>
                                <p>Login ditolak bila belum approved.</p>
                                <p>Admin punya area approve, reject, dan log dasar.</p>
                                <p>Dashboard sudah diarahkan berdasarkan role.</p>
                                <p>Kelas dan membership dasar siap dikelola admin.</p>
                            </div>
                        </div>
                    </div>

                    <AttendanceMonitor monitoring={monitoring} accent="cyan" />
                </div>
            </div>
        </AdminLayout>
    );
}
