import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link } from '@inertiajs/react';

export default function StudentClasses({ classes }) {
    return (
        <StudentLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Kelas Saya
                    </h2>
                    <p className="text-sm text-slate-600">
                        Masuk ke portal kelas untuk melihat guru mapel, teman sekelas, materi, dan tugas.
                    </p>
                </div>
            }
        >
            <Head title="Kelas Saya" />

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
                                    <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">
                                        Siswa
                                    </span>
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
                                        href={route('student.classes.show', schoolClass.id)}
                                        className="inline-flex rounded-full bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900"
                                    >
                                        Masuk
                                    </Link>
                                </div>
                            </div>
                        ))}
                        {classes.length === 0 && (
                            <div className="rounded-2xl bg-white p-5 text-sm text-slate-600 shadow-sm">
                                Belum ada kelas yang ditetapkan untuk akun ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </StudentLayout>
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
