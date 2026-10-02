import TeacherLayout from '@/Layouts/TeacherLayout';
import { Head, Link } from '@inertiajs/react';

export default function TeacherClasses({ classes }) {
    return (
        <TeacherLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Kelas Guru
                    </h2>
                    <p className="text-sm text-slate-600">
                        Portal kelas untuk melihat anggota kelas dan masuk cepat ke absensi, materi, serta tugas.
                    </p>
                </div>
            }
        >
            <Head title="Kelas Guru" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {classes.map((schoolClass) => (
                            <div key={schoolClass.id} className="rounded-2xl bg-white p-5 shadow-sm">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-medium text-slate-900">{schoolClass.name}</p>
                                        <p className="mt-1 text-sm text-slate-600">
                                            {schoolClass.grade_level}
                                            {schoolClass.major ? ` - ${schoolClass.major}` : ''} | {schoolClass.academic_year}
                                        </p>
                                    </div>
                                    <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">
                                        Guru
                                    </span>
                                </div>

                                <div className="mt-3 flex flex-wrap gap-2">
                                    {schoolClass.member_roles.map((role) => (
                                        <span
                                            key={`${schoolClass.id}-${role}`}
                                            className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
                                        >
                                            {role}
                                        </span>
                                    ))}
                                </div>

                                <p className="mt-4 text-sm text-slate-600">
                                    Wali kelas: {schoolClass.homeroom_teacher?.name ?? '-'}
                                </p>

                                <div className="mt-4 grid grid-cols-3 gap-3">
                                    <MiniMetric label="Guru" value={schoolClass.teacher_count} />
                                    <MiniMetric label="Mapel" value={schoolClass.subject_teacher_count} />
                                    <MiniMetric label="Siswa" value={schoolClass.student_count} />
                                </div>

                                <div className="mt-5">
                                    <Link
                                        href={route('teacher.classes.show', schoolClass.id)}
                                        className="inline-flex rounded-full bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                                    >
                                        Masuk
                                    </Link>
                                </div>
                            </div>
                        ))}
                        {classes.length === 0 && (
                            <div className="rounded-2xl bg-white p-5 text-sm text-slate-600 shadow-sm">
                                Belum ada kelas yang ditugaskan untuk akun ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </TeacherLayout>
    );
}

function MiniMetric({ label, value }) {
    return (
        <div className="rounded-xl bg-slate-50 px-3 py-2">
            <p className="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">
                {label}
            </p>
            <p className="mt-1 text-sm font-semibold text-slate-900">{value}</p>
        </div>
    );
}
