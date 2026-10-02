import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link } from '@inertiajs/react';

export default function StudentClassShow({ portal }) {
    const schoolClass = portal.school_class;

    return (
        <StudentLayout
            header={
                <div className="flex flex-col gap-2">
                    <div className="flex flex-wrap items-center gap-3">
                        <Link
                            href={route('student.classes.index')}
                            className="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700"
                        >
                            Kembali
                        </Link>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                            {schoolClass.slug}
                        </span>
                    </div>
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Portal Kelas Siswa
                    </h2>
                    <p className="text-sm text-slate-600">
                        Informasi kelas, guru mapel, teman sekelas, serta akses cepat ke materi dan tugas untuk {schoolClass.name}.
                    </p>
                </div>
            }
        >
            <Head title={`Kelas Saya - ${schoolClass.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <section className="rounded-2xl bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">{schoolClass.name}</h3>
                                <p className="text-sm text-slate-600">
                                    {schoolClass.grade_level}
                                    {schoolClass.major ? ` - ${schoolClass.major}` : ''} | {schoolClass.academic_year}
                                </p>
                                <p className="mt-3 text-sm text-slate-600">
                                    Wali kelas: {schoolClass.homeroom_teacher?.name ?? '-'}
                                </p>
                                {schoolClass.homeroom_teacher?.email && (
                                    <p className="text-sm text-slate-500">{schoolClass.homeroom_teacher.email}</p>
                                )}
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <MiniMetric label="Guru Mapel" value={portal.metrics.subject_teacher_count} />
                                <MiniMetric label="Total Siswa" value={portal.metrics.student_count} />
                                <MiniMetric label="Teman Sekelas" value={portal.metrics.classmate_count} />
                            </div>
                        </div>
                    </section>

                    <section className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Shortcut Domain</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            Masuk cepat ke materi dan tugas yang terkait dengan kelas Anda.
                        </p>
                        <div className="mt-4 flex flex-wrap gap-3">
                            <Link
                                href={route('student.materials.index')}
                                className="inline-flex rounded-full bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900"
                            >
                                Buka Materials
                            </Link>
                            <Link
                                href={route('student.assignments.index')}
                                className="inline-flex rounded-full border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700"
                            >
                                Buka Assignments
                            </Link>
                        </div>
                    </section>

                    <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                        <section className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Guru per Mata Pelajaran</h3>
                            <p className="mt-1 text-sm text-slate-600">
                                Daftar guru yang mengajar mapel di kelas ini.
                            </p>
                            <div className="mt-4 space-y-3">
                                {portal.subject_teachers.map((assignment) => (
                                    <div key={assignment.id} className="rounded-xl border border-slate-200 p-4">
                                        <p className="font-medium text-slate-900">{assignment.subject_name}</p>
                                        <p className="mt-1 text-sm text-slate-600">
                                            {assignment.teacher?.name ?? 'Guru belum ditetapkan'}
                                        </p>
                                        {assignment.teacher?.email && (
                                            <p className="mt-1 text-sm text-slate-500">{assignment.teacher.email}</p>
                                        )}
                                    </div>
                                ))}
                                {portal.subject_teachers.length === 0 && (
                                    <p className="text-sm text-slate-500">Belum ada guru mata pelajaran pada kelas ini.</p>
                                )}
                            </div>
                        </section>

                        <section className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Teman Sekelas</h3>
                            <p className="mt-1 text-sm text-slate-600">
                                Daftar siswa lain yang belajar bersama di kelas ini.
                            </p>
                            <div className="mt-4 grid gap-3 md:grid-cols-2">
                                {portal.classmates.map((student) => (
                                    <div key={student.id} className="rounded-xl border border-slate-200 p-4">
                                        <p className="font-medium text-slate-900">{student.name}</p>
                                        <p className="mt-1 text-sm text-slate-600">{student.email}</p>
                                    </div>
                                ))}
                                {portal.classmates.length === 0 && (
                                    <p className="text-sm text-slate-500">Belum ada teman sekelas lain yang terdaftar.</p>
                                )}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </StudentLayout>
    );
}

function MiniMetric({ label, value }) {
    return (
        <div className="rounded-xl bg-slate-50 px-4 py-3">
            <p className="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">
                {label}
            </p>
            <p className="mt-1 text-lg font-semibold text-slate-900">{value}</p>
        </div>
    );
}
