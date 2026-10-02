import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function AdminMaterials({ classes, materials, flashStatus }) {
    const form = useForm({
        school_class_id: '',
        title: '',
        description: '',
        file: null,
    });

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Materi Kelas
                    </h2>
                    <p className="text-sm text-slate-600">
                        Superadmin dapat mengunggah materi untuk kelas mana pun dan meninjau distribusinya.
                    </p>
                </div>
            }
        >
            <Head title="Materi Kelas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {flashStatus && (
                        <div className="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-medium text-cyan-700">
                            {flashStatus}
                        </div>
                    )}

                    <div className="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Publikasikan materi</h3>
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    form.post(route('admin.materials.store'));
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
                                <Field label="Judul materi" error={form.errors.title}>
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
                                <Field label="File materi" error={form.errors.file}>
                                    <input
                                        type="file"
                                        onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                                        className="mt-1 block w-full text-sm text-slate-600"
                                    />
                                </Field>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="inline-flex rounded-full bg-cyan-700 px-4 py-2 text-sm font-medium text-white"
                                >
                                    Upload materi
                                </button>
                            </form>
                        </div>

                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Daftar materi</h3>
                            <div className="mt-4 space-y-4">
                                {materials.map((material) => (
                                    <div key={material.id} className="rounded-2xl border border-slate-200 p-4">
                                        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <p className="font-medium text-slate-900">{material.title}</p>
                                                <p className="mt-1 text-sm text-slate-600">
                                                    {material.school_class.name} | {material.file_name}
                                                </p>
                                                <p className="mt-1 text-sm text-slate-600">
                                                    Pengunggah: {material.teacher_name || '-'}
                                                </p>
                                                <p className="mt-1 text-sm text-slate-600">
                                                    Dipublikasikan {material.published_at ? new Date(material.published_at).toLocaleString('id-ID') : '-'}
                                                </p>
                                            </div>
                                            <a
                                                href={material.download_url}
                                                className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                                            >
                                                Download
                                            </a>
                                        </div>
                                        {material.description && (
                                            <p className="mt-3 text-sm leading-6 text-slate-600">{material.description}</p>
                                        )}
                                    </div>
                                ))}
                                {materials.length === 0 && (
                                    <p className="text-sm text-slate-500">Belum ada materi yang dipublikasikan.</p>
                                )}
                            </div>
                        </div>
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
