import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function ClassShow({ schoolClass, teachers, students, status }) {
    const teacherForm = useForm({ user_id: '' });
    const studentForm = useForm({ user_id: '' });
    const subjectTeacherForm = useForm({
        teacher_id: '',
        subject_name: '',
    });

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <div className="flex flex-wrap items-center gap-3">
                        <Link
                            href={route('admin.classes.index')}
                            className="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700"
                        >
                            Kembali
                        </Link>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                            {schoolClass.slug}
                        </span>
                    </div>
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Detail Kelas
                    </h2>
                    <p className="text-sm text-slate-600">
                        Kelola membership guru, guru mata pelajaran, dan siswa untuk {schoolClass.name}.
                    </p>
                </div>
            }
        >
            <Head title={`Detail Kelas - ${schoolClass.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {status && (
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {status}
                        </div>
                    )}

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {schoolClass.name}
                                </h3>
                                <p className="text-sm text-slate-600">
                                    {schoolClass.grade_level}
                                    {schoolClass.major ? ` - ${schoolClass.major}` : ''} | {schoolClass.academic_year}
                                </p>
                                <p className="mt-2 text-sm text-slate-600">
                                    Wali kelas: {schoolClass.homeroom_teacher?.name || '-'}
                                </p>
                                {schoolClass.homeroom_teacher?.email && (
                                    <p className="text-sm text-slate-500">
                                        {schoolClass.homeroom_teacher.email}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <MiniMetric label="Guru Umum" value={schoolClass.teachers.length} />
                                <MiniMetric label="Guru Mapel" value={schoolClass.subject_teachers.length} />
                                <MiniMetric label="Siswa" value={schoolClass.students.length} />
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                        <div className="space-y-6">
                            <div className="rounded-2xl bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Guru Umum Kelas
                                </h3>
                                <p className="mt-1 text-sm text-slate-600">
                                    Membership guru umum tetap dipertahankan untuk kebutuhan kelas yang lebih luas.
                                </p>

                                <form
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        teacherForm.post(route('admin.classes.teachers.store', schoolClass.id), {
                                            preserveScroll: true,
                                            onSuccess: () => teacherForm.reset(),
                                        });
                                    }}
                                    className="mt-4 flex flex-col gap-3 md:flex-row"
                                >
                                    <select
                                        value={teacherForm.data.user_id}
                                        onChange={(e) => teacherForm.setData('user_id', e.target.value)}
                                        className="block w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    >
                                        <option value="">Pilih guru</option>
                                        {teachers.map((teacher) => (
                                            <option key={teacher.id} value={teacher.id}>
                                                {teacher.name} - {teacher.email}
                                            </option>
                                        ))}
                                    </select>
                                    <button
                                        className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                        disabled={teacherForm.processing}
                                    >
                                        Tambah Guru
                                    </button>
                                </form>
                                {teacherForm.errors.user_id && (
                                    <p className="mt-2 text-sm text-rose-600">{teacherForm.errors.user_id}</p>
                                )}

                                <div className="mt-4 space-y-2">
                                    {schoolClass.teachers.map((teacher) => (
                                        <div key={teacher.id} className="rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                                            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                <div>
                                                    <p className="font-medium text-slate-900">{teacher.name}</p>
                                                    <p className="mt-1">{teacher.email}</p>
                                                    <p className="mt-2 text-xs uppercase tracking-[0.14em] text-slate-500">
                                                        {teacher.roles.join(', ')}
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        router.delete(
                                                            route('admin.classes.teachers.destroy', [schoolClass.id, teacher.id]),
                                                            { preserveScroll: true },
                                                        )
                                                    }
                                                    className="rounded-full border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                    {schoolClass.teachers.length === 0 && (
                                        <p className="text-sm text-slate-500">Belum ada guru umum pada kelas ini.</p>
                                    )}
                                </div>
                            </div>

                            <div className="rounded-2xl bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Daftar Siswa
                                </h3>
                                <p className="mt-1 text-sm text-slate-600">
                                    Tambahkan siswa ke kelas dan lihat daftar siswa yang sudah terdaftar.
                                </p>

                                <form
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        studentForm.post(route('admin.classes.students.store', schoolClass.id), {
                                            preserveScroll: true,
                                            onSuccess: () => studentForm.reset(),
                                        });
                                    }}
                                    className="mt-4 flex flex-col gap-3 md:flex-row"
                                >
                                    <select
                                        value={studentForm.data.user_id}
                                        onChange={(e) => studentForm.setData('user_id', e.target.value)}
                                        className="block w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    >
                                        <option value="">Pilih siswa</option>
                                        {students.map((student) => (
                                            <option key={student.id} value={student.id}>
                                                {student.name} - {student.email}
                                            </option>
                                        ))}
                                    </select>
                                    <button
                                        className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                        disabled={studentForm.processing}
                                    >
                                        Tambah Siswa
                                    </button>
                                </form>
                                {studentForm.errors.user_id && (
                                    <p className="mt-2 text-sm text-rose-600">{studentForm.errors.user_id}</p>
                                )}

                                <div className="mt-4 space-y-2">
                                    {schoolClass.students.map((student) => (
                                        <div key={student.id} className="rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                                            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                <div>
                                                    <p className="font-medium text-slate-900">{student.name}</p>
                                                    <p className="mt-1">{student.email}</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        router.delete(
                                                            route('admin.classes.students.destroy', [schoolClass.id, student.id]),
                                                            { preserveScroll: true },
                                                        )
                                                    }
                                                    className="rounded-full border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                    {schoolClass.students.length === 0 && (
                                        <p className="text-sm text-slate-500">Belum ada siswa pada kelas ini.</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Guru Mata Pelajaran
                            </h3>
                            <p className="mt-1 text-sm text-slate-600">
                                Tetapkan guru untuk mapel tertentu pada kelas ini.
                            </p>

                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    subjectTeacherForm.post(route('admin.classes.subject-teachers.store', schoolClass.id), {
                                        preserveScroll: true,
                                        onSuccess: () => subjectTeacherForm.reset(),
                                    });
                                }}
                                className="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto]"
                            >
                                <Field label="Mata pelajaran" error={subjectTeacherForm.errors.subject_name}>
                                    <input
                                        value={subjectTeacherForm.data.subject_name}
                                        onChange={(e) => subjectTeacherForm.setData('subject_name', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        placeholder="Contoh: Matematika"
                                        required
                                    />
                                </Field>
                                <Field label="Guru" error={subjectTeacherForm.errors.teacher_id}>
                                    <select
                                        value={subjectTeacherForm.data.teacher_id}
                                        onChange={(e) => subjectTeacherForm.setData('teacher_id', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    >
                                        <option value="">Pilih guru</option>
                                        {teachers.map((teacher) => (
                                            <option key={teacher.id} value={teacher.id}>
                                                {teacher.name} - {teacher.email}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <div className="flex items-end">
                                    <button
                                        className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                        disabled={subjectTeacherForm.processing}
                                    >
                                        Simpan Mapel
                                    </button>
                                </div>
                            </form>

                            <div className="mt-6 space-y-3">
                                {schoolClass.subject_teachers.map((assignment) => (
                                    <SubjectTeacherRow
                                        key={assignment.id}
                                        schoolClass={schoolClass}
                                        assignment={assignment}
                                        teachers={teachers}
                                    />
                                ))}
                                {schoolClass.subject_teachers.length === 0 && (
                                    <p className="text-sm text-slate-500">Belum ada guru mata pelajaran pada kelas ini.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function SubjectTeacherRow({ schoolClass, assignment, teachers }) {
    const form = useForm({
        subject_name: assignment.subject_name,
        teacher_id: assignment.teacher?.id ? String(assignment.teacher.id) : '',
    });

    return (
        <div className="rounded-2xl border border-slate-200 p-4">
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.patch(
                        route('admin.classes.subject-teachers.update', [schoolClass.id, assignment.id]),
                        { preserveScroll: true },
                    );
                }}
                className="grid gap-3 lg:grid-cols-[1fr_1fr_auto_auto]"
            >
                <Field label="Mata pelajaran" error={form.errors.subject_name}>
                    <input
                        value={form.data.subject_name}
                        onChange={(e) => form.setData('subject_name', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                </Field>
                <Field label="Guru" error={form.errors.teacher_id}>
                    <select
                        value={form.data.teacher_id}
                        onChange={(e) => form.setData('teacher_id', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                        <option value="">Pilih guru</option>
                        {teachers.map((teacher) => (
                            <option key={teacher.id} value={teacher.id}>
                                {teacher.name} - {teacher.email}
                            </option>
                        ))}
                    </select>
                </Field>
                <div className="flex items-end">
                    <button
                        className="w-full rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                        disabled={form.processing}
                    >
                        Perbarui
                    </button>
                </div>
                <div className="flex items-end">
                    <button
                        type="button"
                        onClick={() =>
                            router.delete(
                                route('admin.classes.subject-teachers.destroy', [schoolClass.id, assignment.id]),
                                { preserveScroll: true },
                            )
                        }
                        className="w-full rounded-full border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700"
                    >
                        Hapus
                    </button>
                </div>
            </form>
            {assignment.teacher?.email && (
                <p className="mt-3 text-sm text-slate-500">
                    Saat ini: {assignment.teacher.name} ({assignment.teacher.email})
                </p>
            )}
        </div>
    );
}

function Field({ label, error, children }) {
    return (
        <label className="block text-sm font-medium text-slate-700">
            {label}
            {children}
            {error && <p className="mt-1 text-sm text-rose-600">{error}</p>}
        </label>
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
