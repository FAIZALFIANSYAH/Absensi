import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ClassIndex({ classes, teachers, status }) {
    const classForm = useForm({
        name: '',
        grade_level: '',
        major: '',
        academic_year: '',
        homeroom_teacher_id: '',
    });

    const submitClass = (event) => {
        event.preventDefault();
        classForm.post(route('admin.classes.store'), {
            onSuccess: () => classForm.reset(),
        });
    };

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Manajemen Kelas
                    </h2>
                    <p className="text-sm text-slate-600">
                        Buat kelas baru dan masuk ke detail kelas untuk mengelola guru serta siswa.
                    </p>
                </div>
            }
        >
            <Head title="Manajemen Kelas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {status && (
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {status}
                        </div>
                    )}

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Buat kelas baru
                        </h3>
                        <form onSubmit={submitClass} className="mt-4 grid gap-4 md:grid-cols-2">
                            <Field label="Nama kelas" error={classForm.errors.name}>
                                <input
                                    value={classForm.data.name}
                                    onChange={(e) => classForm.setData('name', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                            </Field>
                            <Field label="Tingkat" error={classForm.errors.grade_level}>
                                <input
                                    value={classForm.data.grade_level}
                                    onChange={(e) => classForm.setData('grade_level', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    placeholder="X, XI, XII"
                                    required
                                />
                            </Field>
                            <Field label="Jurusan" error={classForm.errors.major}>
                                <input
                                    value={classForm.data.major}
                                    onChange={(e) => classForm.setData('major', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Tahun akademik" error={classForm.errors.academic_year}>
                                <input
                                    value={classForm.data.academic_year}
                                    onChange={(e) => classForm.setData('academic_year', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    placeholder="2026/2027"
                                    required
                                />
                            </Field>
                            <Field label="Wali kelas" error={classForm.errors.homeroom_teacher_id}>
                                <select
                                    value={classForm.data.homeroom_teacher_id}
                                    onChange={(e) => classForm.setData('homeroom_teacher_id', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Pilih guru</option>
                                    {teachers.map((teacher) => (
                                        <option key={teacher.id} value={teacher.id}>
                                            {teacher.name} - {teacher.email}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <div className="md:col-span-2">
                                <button
                                    type="submit"
                                    className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                    disabled={classForm.processing}
                                >
                                    Simpan Kelas
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="space-y-4">
                        {classes.map((schoolClass) => (
                            <div key={schoolClass.id} className="rounded-2xl bg-white p-6 shadow-sm">
                                <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 className="text-lg font-semibold text-slate-900">
                                            {schoolClass.name}
                                        </h3>
                                        <p className="text-sm text-slate-600">
                                            {schoolClass.grade_level}
                                            {schoolClass.major ? ` - ${schoolClass.major}` : ''} | {schoolClass.academic_year}
                                        </p>
                                        <p className="mt-1 text-sm text-slate-600">
                                            Wali kelas: {schoolClass.homeroom_teacher?.name || '-'}
                                        </p>
                                    </div>

                                    <div className="flex flex-col items-start gap-3 md:items-end">
                                        <div className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                                            {schoolClass.slug}
                                        </div>
                                        <Link
                                            href={route('admin.classes.show', schoolClass.id)}
                                            className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                        >
                                            Masuk
                                        </Link>
                                    </div>
                                </div>

                                <div className="mt-6 grid gap-3 md:grid-cols-3">
                                    <MiniMetric label="Guru Umum" value={schoolClass.teacher_count} />
                                    <MiniMetric label="Guru Mapel" value={schoolClass.subject_count} />
                                    <MiniMetric label="Siswa" value={schoolClass.student_count} />
                                </div>
                            </div>
                        ))}

                        {classes.length === 0 && (
                            <div className="rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm">
                                Belum ada kelas yang dibuat.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
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
