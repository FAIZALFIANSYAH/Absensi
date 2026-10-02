import TeacherLayout from '@/Layouts/TeacherLayout';
import { Head } from '@inertiajs/react';
import AttendanceMonitor from '@/Components/Attendance/AttendanceMonitor';

export default function TeacherDashboard({ profile, classes, monitoring }) {
    return (
        <TeacherLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Dashboard Guru
                    </h2>
                    <p className="text-sm text-slate-600">
                        Monitoring kehadiran harian dan kelas yang diampu tersedia di satu area kerja.
                    </p>
                </div>
            }
        >
            <Head title="Dashboard Guru" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Profil guru
                        </h3>
                        <div className="mt-4 grid gap-4 md:grid-cols-3">
                            <Info label="NIP" value={profile?.nip} />
                            <Info label="NIK" value={profile?.nik} />
                            <Info label="Telepon" value={profile?.phone} />
                            <Info
                                label="Gender"
                                value={
                                    profile?.gender === 'male'
                                        ? 'Laki-laki'
                                        : profile?.gender === 'female'
                                          ? 'Perempuan'
                                          : '-'
                                }
                            />
                            <Info label="Tanggal lahir" value={profile?.birth_date} />
                            <Info label="Alamat" value={profile?.address} />
                        </div>
                    </div>

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Kelas yang diampu</h3>
                        <div className="mt-4 grid gap-4 md:grid-cols-3">
                            {classes.map((schoolClass) => (
                                <div key={schoolClass.id} className="rounded-2xl border border-slate-200 p-4">
                                    <p className="font-medium text-slate-900">{schoolClass.name}</p>
                                    <p className="mt-1 text-sm text-slate-600">
                                        {schoolClass.grade_level}
                                        {schoolClass.major ? ` - ${schoolClass.major}` : ''}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-600">
                                        {schoolClass.academic_year} | {schoolClass.member_role}
                                    </p>
                                </div>
                            ))}
                            {classes.length === 0 && (
                                <p className="text-sm text-slate-500">Belum ada kelas yang ditugaskan untuk akun ini.</p>
                            )}
                        </div>
                    </div>

                    <AttendanceMonitor monitoring={monitoring} accent="emerald" />
                </div>
            </div>
        </TeacherLayout>
    );
}

function Info({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 p-4">
            <p className="text-sm font-medium text-slate-900">{label}</p>
            <p className="mt-1 text-sm text-slate-600">{value || '-'}</p>
        </div>
    );
}
