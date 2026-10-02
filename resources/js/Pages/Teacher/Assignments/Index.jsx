import TeacherLayout from '@/Layouts/TeacherLayout';
import { Head, useForm } from '@inertiajs/react';

export default function TeacherAssignments({ classes, assignments, flashStatus }) {
    const form = useForm({
        school_class_id: '',
        title: '',
        description: '',
        deadline_at: '',
        file: null,
    });

    return (
        <TeacherLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Tugas Kelas
                    </h2>
                    <p className="text-sm text-slate-600">
                        Guru dapat membuat tugas per kelas, menentukan deadline, dan memantau pengumpulan siswa.
                    </p>
                </div>
            }
        >
            <Head title="Tugas Kelas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {flashStatus && (
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {flashStatus}
                        </div>
                    )}

                    <div className="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Publikasikan tugas</h3>
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    form.post(route('teacher.assignments.store'));
                                }}
                                className="mt-4 space-y-4"
                            >
                                <Field label="Kelas" error={form.errors.school_class_id}>
                                    <select
                                        value={form.data.school_class_id}
                                        onChange={(event) => form.setData('school_class_id', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                        <option value="">Pilih kelas</option>
                                        {classes.map((schoolClass) => (
                                            <option key={schoolClass.id} value={schoolClass.id}>
                                                {schoolClass.name}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <Field label="Judul tugas" error={form.errors.title}>
                                    <input
                                        value={form.data.title}
                                        onChange={(event) => form.setData('title', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Deskripsi" error={form.errors.description}>
                                    <textarea
                                        value={form.data.description}
                                        onChange={(event) => form.setData('description', event.target.value)}
                                        rows="4"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Deadline" error={form.errors.deadline_at}>
                                    <input
                                        type="datetime-local"
                                        value={form.data.deadline_at}
                                        onChange={(event) => form.setData('deadline_at', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Lampiran tugas" error={form.errors.file}>
                                    <input
                                        type="file"
                                        onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                                        className="mt-1 block w-full text-sm text-slate-600"
                                    />
                                </Field>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="inline-flex rounded-full bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                                >
                                    Publikasikan tugas
                                </button>
                            </form>
                        </div>

                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Daftar tugas</h3>
                            <div className="mt-4 space-y-4">
                                {assignments.map((assignment) => (
                                    <div key={assignment.id} className="rounded-2xl border border-slate-200 p-4">
                                        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <p className="font-medium text-slate-900">{assignment.title}</p>
                                                <p className="mt-1 text-sm text-slate-600">
                                                    {assignment.school_class.name} | Deadline {assignment.deadline_at ? new Date(assignment.deadline_at).toLocaleString('id-ID') : '-'}
                                                </p>
                                                <p className="mt-1 text-sm text-slate-600">
                                                    Pengumpulan: {assignment.submission_count}
                                                </p>
                                            </div>
                                            {assignment.download_url && (
                                                <a
                                                    href={assignment.download_url}
                                                    className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                                                >
                                                    Download lampiran
                                                </a>
                                            )}
                                        </div>
                                        {assignment.description && (
                                            <p className="mt-3 text-sm leading-6 text-slate-600">{assignment.description}</p>
                                        )}
                                        <div className="mt-4 space-y-2">
                                            {assignment.submissions.map((submission) => (
                                                <div key={submission.id} className="rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                                                    <p className="font-medium text-slate-900">{submission.student_name}</p>
                                                    <p className="mt-1">
                                                        {submission.nis ? `NIS ${submission.nis} | ` : ''}
                                                        {submission.status} | {submission.submitted_at ? new Date(submission.submitted_at).toLocaleString('id-ID') : '-'}
                                                    </p>
                                                </div>
                                            ))}
                                            {assignment.submissions.length === 0 && (
                                                <p className="text-sm text-slate-500">Belum ada siswa yang mengumpulkan.</p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                                {assignments.length === 0 && (
                                    <p className="text-sm text-slate-500">Belum ada tugas yang dipublikasikan.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </TeacherLayout>
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
