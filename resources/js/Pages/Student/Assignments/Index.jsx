import StudentLayout from '@/Layouts/StudentLayout';
import { Head, useForm } from '@inertiajs/react';

export default function StudentAssignments({ assignments, flashStatus }) {
    const form = useForm({
        file: null,
        notes: '',
    });

    function submitAssignment(assignmentId) {
        form.post(route('student.assignments.submit', assignmentId), {
            preserveScroll: true,
            onSuccess: () => form.reset('file', 'notes'),
        });
    }

    return (
        <StudentLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Tugas Saya
                    </h2>
                    <p className="text-sm text-slate-600">
                        Siswa dapat melihat tugas kelas, mengunduh lampiran, dan mengumpulkan file sebelum deadline.
                    </p>
                </div>
            }
        >
            <Head title="Tugas Saya" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {flashStatus && (
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {flashStatus}
                        </div>
                    )}

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Daftar tugas kelas</h3>
                        <div className="mt-4 space-y-4">
                            {assignments.map((assignment) => (
                                <div key={assignment.id} className="rounded-2xl border border-slate-200 p-4">
                                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <p className="font-medium text-slate-900">{assignment.title}</p>
                                            <p className="mt-1 text-sm text-slate-600">
                                                {assignment.school_class.name} | Deadline {assignment.deadline_at ? new Date(assignment.deadline_at).toLocaleString('id-ID') : '-'}
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

                                    {assignment.submission ? (
                                        <div className="mt-4 rounded-2xl bg-amber-50 p-4">
                                            <p className="font-medium text-slate-900">Tugas sudah dikumpulkan</p>
                                            <p className="mt-1 text-sm text-slate-600">
                                                {assignment.submission.file_name} | {assignment.submission.status} | {assignment.submission.submitted_at ? new Date(assignment.submission.submitted_at).toLocaleString('id-ID') : '-'}
                                            </p>
                                            {assignment.submission.notes && (
                                                <p className="mt-1 text-sm text-slate-600">{assignment.submission.notes}</p>
                                            )}
                                            <a
                                                href={assignment.submission.download_url}
                                                className="mt-3 inline-flex rounded-full bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900"
                                            >
                                                Download submission saya
                                            </a>
                                        </div>
                                    ) : (
                                        <form
                                            onSubmit={(event) => {
                                                event.preventDefault();
                                                submitAssignment(assignment.id);
                                            }}
                                            className="mt-4 space-y-4 rounded-2xl bg-slate-50 p-4"
                                        >
                                            <Field label="File jawaban" error={form.errors.file}>
                                                <input
                                                    type="file"
                                                    onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                                                    className="mt-1 block w-full text-sm text-slate-600"
                                                />
                                            </Field>
                                            <Field label="Catatan" error={form.errors.notes}>
                                                <textarea
                                                    value={form.data.notes}
                                                    onChange={(event) => form.setData('notes', event.target.value)}
                                                    rows="3"
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                />
                                            </Field>
                                            <button
                                                type="submit"
                                                disabled={form.processing}
                                                className="inline-flex rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                                            >
                                                Kumpulkan tugas
                                            </button>
                                        </form>
                                    )}
                                </div>
                            ))}
                            {assignments.length === 0 && (
                                <p className="text-sm text-slate-500">Belum ada tugas untuk kelas Anda saat ini.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </StudentLayout>
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
