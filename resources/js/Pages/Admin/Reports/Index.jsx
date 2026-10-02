import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';

const statusTone = {
    present: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    late: 'bg-amber-50 text-amber-700 border-amber-200',
    excused: 'bg-sky-50 text-sky-700 border-sky-200',
    absent: 'bg-rose-50 text-rose-700 border-rose-200',
    unrecorded: 'bg-slate-100 text-slate-700 border-slate-200',
};

export default function AttendanceReportIndex({ report }) {
    const filterForm = useForm({
        school_class_id: report.filters.school_class_id,
        session_date: report.filters.session_date,
        session_status: report.filters.session_status,
    });

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.reports.index'), filterForm.data, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Laporan Kehadiran
                    </h2>
                    <p className="text-sm text-slate-600">
                        Laporan kehadiran per kelas dan per tanggal untuk membantu pengujian operasional serta review data absensi.
                    </p>
                </div>
            }
        >
            <Head title="Laporan Kehadiran" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Filter laporan</h3>
                        <form onSubmit={applyFilters} className="mt-4 grid gap-4 md:grid-cols-[1.1fr_0.9fr_0.9fr_auto]">
                            <Field label="Kelas">
                                <select
                                    value={filterForm.data.school_class_id}
                                    onChange={(event) => filterForm.setData('school_class_id', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Semua kelas</option>
                                    {report.filter_options.classes.map((schoolClass) => (
                                        <option key={schoolClass.id} value={schoolClass.id}>
                                            {schoolClass.name} ({schoolClass.academic_year})
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Tanggal">
                                <input
                                    type="date"
                                    value={filterForm.data.session_date}
                                    onChange={(event) => filterForm.setData('session_date', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Status sesi">
                                <select
                                    value={filterForm.data.session_status}
                                    onChange={(event) => filterForm.setData('session_status', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    {report.filter_options.session_statuses.map((status) => (
                                        <option key={status.value} value={status.value}>
                                            {status.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <div className="flex items-end">
                                <button className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                                    Terapkan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                        <MetricCard label="Sesi" value={report.overview.session_count} />
                        <MetricCard label="Target Siswa" value={report.overview.student_count} />
                        <MetricCard label="Tercatat" value={report.overview.recorded_count} />
                        <MetricCard label="Belum Tercatat" value={report.overview.unrecorded_count} />
                        <MetricCard label="Hadir" value={report.overview.present_count} />
                        <MetricCard label="Terlambat" value={report.overview.late_count} />
                    </div>

                    <div className="space-y-4">
                        {report.sessions.map((session) => (
                            <div key={session.id} className="rounded-2xl bg-white p-6 shadow-sm">
                                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <h3 className="text-lg font-semibold text-slate-900">
                                            {session.school_class.name}
                                        </h3>
                                        <p className="text-sm text-slate-600">
                                            {session.school_class.grade_level}
                                            {session.school_class.major ? ` - ${session.school_class.major}` : ''} | {session.school_class.academic_year}
                                        </p>
                                        <p className="mt-1 text-sm text-slate-600">
                                            {session.session_date} | Mulai {session.start_time}
                                            {session.end_time ? ` | Selesai ${session.end_time}` : ''}
                                            {' '}| Dibuka oleh {session.opener}
                                        </p>
                                        {session.notes && (
                                            <p className="mt-2 text-sm text-slate-500">{session.notes}</p>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                                            {session.status_label}
                                        </span>
                                    </div>
                                </div>

                                <div className="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                                    <MetricCard label="Target" value={session.student_count} compact />
                                    <MetricCard label="Tercatat" value={session.recorded_count} compact />
                                    <MetricCard label="Belum Tercatat" value={session.unrecorded_count} compact />
                                    <MetricCard label="Izin" value={session.status_breakdown.excused} compact />
                                    <MetricCard label="Absen" value={session.status_breakdown.absent} compact />
                                </div>

                                <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        className="h-full rounded-full bg-cyan-500 transition-all"
                                        style={{ width: `${session.progress_percent}%` }}
                                    />
                                </div>

                                <div className="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    {session.students.map((student) => (
                                        <div key={student.id} className="rounded-2xl border border-slate-200 p-4">
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <p className="font-medium text-slate-900">{student.name}</p>
                                                    <p className="mt-1 text-sm text-slate-500">
                                                        NIS: {student.nis || '-'}
                                                    </p>
                                                </div>
                                                <span className={`rounded-full border px-3 py-1 text-xs font-semibold ${statusTone[student.status] || statusTone.unrecorded}`}>
                                                    {student.status_label}
                                                </span>
                                            </div>
                                            <div className="mt-3 space-y-1 text-sm text-slate-600">
                                                <p>
                                                    Waktu: {student.scanned_at ? new Date(student.scanned_at).toLocaleString('id-ID') : '-'}
                                                </p>
                                                <p>
                                                    Scanner: {student.scanner_name || '-'}
                                                </p>
                                                <p>
                                                    Catatan: {student.notes || '-'}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}

                        {report.sessions.length === 0 && (
                            <div className="rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm">
                                Belum ada sesi yang cocok dengan filter laporan saat ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function Field({ label, children }) {
    return (
        <label className="block text-sm font-medium text-slate-700">
            {label}
            {children}
        </label>
    );
}

function MetricCard({ label, value, compact = false }) {
    return (
        <div className={`rounded-2xl bg-white shadow-sm ${compact ? 'border border-slate-200 p-4' : 'p-5'}`}>
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-semibold text-slate-900">{value}</p>
        </div>
    );
}
