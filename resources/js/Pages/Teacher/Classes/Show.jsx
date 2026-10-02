import TeacherLayout from '@/Layouts/TeacherLayout';
import { Head, Link } from '@inertiajs/react';

export default function TeacherClassShow({ portal }) {
    const schoolClass = portal.school_class;

    return (
        <TeacherLayout
            header={
                <div className="flex flex-col gap-2">
                    <div className="flex flex-wrap items-center gap-3">
                        <Link
                            href={route('teacher.classes.index')}
                            className="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700"
                        >
                            Kembali
                        </Link>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                            {schoolClass.slug}
                        </span>
                    </div>
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Portal Kelas Guru
                    </h2>
                    <p className="text-sm text-slate-600">
                        Ringkasan kelas, guru yang terlibat, siswa, dan akses cepat ke domain pembelajaran untuk {schoolClass.name}.
                    </p>
                </div>
            }
        >
            <Head title={`Kelas Guru - ${schoolClass.name}`} />

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
                                <MiniMetric label="Guru" value={portal.metrics.teacher_count} />
                                <MiniMetric label="Mapel" value={portal.metrics.subject_teacher_count} />
                                <MiniMetric label="Siswa" value={portal.metrics.student_count} />
                            </div>
                        </div>
                    </section>

                    <section className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Shortcut Domain</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            Gunakan pintasan ini untuk masuk ke area kerja guru yang paling sering dipakai.
                        </p>
                        <div className="mt-4 flex flex-wrap gap-3">
                            <Link
                                href={route('teacher.attendance.index')}
                                className="inline-flex rounded-full bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                            >
                                Buka Attendance
                            </Link>
                            <Link
                                href={route('teacher.materials.index')}
                                className="inline-flex rounded-full border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-700"
                            >
                                Buka Materials
                            </Link>
                            <Link
                                href={route('teacher.assignments.index')}
                                className="inline-flex rounded-full border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-700"
                            >
                                Buka Assignments
                            </Link>
                        </div>
                    </section>

                    <div className="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
                        <section className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Guru di Kelas</h3>
                            <p className="mt-1 text-sm text-slate-600">
                                Daftar guru umum dan wali kelas yang memiliki membership pada kelas ini.
                            </p>
                            <div className="mt-4 space-y-3">
                                {portal.teachers.map((teacher) => (
                                    <div key={teacher.id} className="rounded-xl border border-slate-200 p-4">
                                        <p className="font-medium text-slate-900">{teacher.name}</p>
                                        <p className="mt-1 text-sm text-slate-600">{teacher.email}</p>
                                        <div className="mt-3 flex flex-wrap gap-2">
                                            {teacher.role_labels.map((role) => (
                                                <span
                                                    key={`${teacher.id}-${role}`}
                                                    className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
                                                >
                                                    {role}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                                {portal.teachers.length === 0 && (
                                    <p className="text-sm text-slate-500">Belum ada guru umum yang terdaftar pada kelas ini.</p>
                                )}
                            </div>
                        </section>

                        <section className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Guru per Mata Pelajaran</h3>
                            <p className="mt-1 text-sm text-slate-600">
                                Penugasan guru mata pelajaran untuk kelas ini.
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
                    </div>

                    <section className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Siswa di Kelas</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            Daftar seluruh siswa yang sudah menjadi anggota kelas ini.
                        </p>
                        <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            {portal.students.map((student) => (
                                <div key={student.id} className="rounded-xl border border-slate-200 p-4">
                                    <p className="font-medium text-slate-900">{student.name}</p>
                                    <p className="mt-1 text-sm text-slate-600">{student.email}</p>
                                </div>
                            ))}
                            {portal.students.length === 0 && (
                                <p className="text-sm text-slate-500">Belum ada siswa yang terdaftar pada kelas ini.</p>
                            )}
                        </div>
                    </section>
                </div>
            </div>
        </TeacherLayout>
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
