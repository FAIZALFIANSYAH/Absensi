import StudentLayout from '@/Layouts/StudentLayout';
import { Head } from '@inertiajs/react';

export default function StudentMaterials({ materials }) {
    return (
        <StudentLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Materi Belajar
                    </h2>
                    <p className="text-sm text-slate-600">
                        Siswa dapat melihat dan mengunduh materi dari kelas yang diikuti.
                    </p>
                </div>
            }
        >
            <Head title="Materi Belajar" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Daftar materi kelas</h3>
                        <div className="mt-4 space-y-4">
                            {materials.map((material) => (
                                <div key={material.id} className="rounded-2xl border border-slate-200 p-4">
                                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <p className="font-medium text-slate-900">{material.title}</p>
                                            <p className="mt-1 text-sm text-slate-600">
                                                {material.school_class.name} | {material.teacher_name}
                                            </p>
                                            <p className="mt-1 text-sm text-slate-600">
                                                {material.file_name} | {material.published_at ? new Date(material.published_at).toLocaleString('id-ID') : '-'}
                                            </p>
                                        </div>
                                        <a
                                            href={material.download_url}
                                            className="inline-flex rounded-full bg-amber-500 px-4 py-2 text-sm font-medium text-slate-900"
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
                                <p className="text-sm text-slate-500">Belum ada materi untuk kelas Anda saat ini.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </StudentLayout>
    );
}
