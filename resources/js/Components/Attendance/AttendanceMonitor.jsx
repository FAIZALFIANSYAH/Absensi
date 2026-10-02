import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function AttendanceMonitor({ monitoring, accent = 'emerald' }) {
    const [filters, setFilters] = useState(monitoring.filters);

    useEffect(() => {
        setFilters(monitoring.filters);
    }, [monitoring.filters]);

    useEffect(() => {
        if (!monitoring.overview.has_open_session) {
            return undefined;
        }

        const timer = window.setInterval(() => {
            router.reload({
                only: ['monitoring'],
                preserveScroll: true,
                preserveState: true,
            });
        }, monitoring.overview.poll_interval_seconds * 1000);

        return () => window.clearInterval(timer);
    }, [monitoring.overview.has_open_session, monitoring.overview.poll_interval_seconds]);

    function applyFilters(event) {
        event.preventDefault();

        router.get(route('dashboard'), filters, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['monitoring'],
        });
    }

    const accentClass = accent === 'cyan'
        ? 'bg-cyan-500'
        : 'bg-emerald-500';

    return (
        <div className="space-y-6">
            <div className="rounded-2xl bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">Monitoring kehadiran</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            Pantau siswa yang sudah scan dan yang belum hadir pada sesi yang dipilih.
                        </p>
                    </div>
                    <div className="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                        {monitoring.overview.has_open_session ? 'Polling aktif' : 'Polling standby'}
                    </div>
                </div>

                <form onSubmit={applyFilters} className="mt-5 grid gap-4 lg:grid-cols-[1.1fr_0.9fr_0.9fr_auto]">
                    <Field label="Kelas">
                        <select
                            value={filters.school_class_id}
                            onChange={(event) => setFilters((current) => ({
                                ...current,
                                school_class_id: event.target.value,
                            }))}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Semua kelas</option>
                            {monitoring.filter_options.classes.map((schoolClass) => (
                                <option key={schoolClass.id} value={schoolClass.id}>
                                    {schoolClass.name}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <Field label="Tanggal">
                        <input
                            type="date"
                            value={filters.session_date}
                            onChange={(event) => setFilters((current) => ({
                                ...current,
                                session_date: event.target.value,
                            }))}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        />
                    </Field>
                    <Field label="Status sesi">
                        <select
                            value={filters.status}
                            onChange={(event) => setFilters((current) => ({
                                ...current,
                                status: event.target.value,
                            }))}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            {monitoring.filter_options.statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <div className="flex items-end">
                        <button
                            type="submit"
                            className="inline-flex rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                        >
                            Terapkan filter
                        </button>
                    </div>
                </form>
            </div>

            <div className="grid gap-4 md:grid-cols-4">
                <Metric label="Sesi terpantau" value={monitoring.overview.session_count} />
                <Metric label="Sesi aktif" value={monitoring.overview.open_session_count} />
                <Metric label="Sudah scan" value={monitoring.overview.scanned_student_count} />
                <Metric label="Belum scan" value={monitoring.overview.pending_student_count} />
            </div>

            {monitoring.sessions.length === 0 ? (
                <div className="rounded-2xl bg-white p-6 shadow-sm">
                    <p className="text-sm text-slate-500">
                        Belum ada sesi absensi yang cocok dengan filter ini.
                    </p>
                </div>
            ) : (
                <div className="space-y-4">
                    {monitoring.sessions.map((session) => (
                        <div key={session.id} className="rounded-2xl bg-white p-6 shadow-sm">
                            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div className="space-y-2">
                                    <div>
                                        <h4 className="text-lg font-semibold text-slate-900">
                                            {session.school_class.name}
                                        </h4>
                                        <p className="mt-1 text-sm text-slate-600">
                                            {session.session_date} | Mulai {session.start_time}
                                            {session.end_time ? ` | Selesai ${session.end_time}` : ''}
                                            {' '}| Dibuka oleh {session.opener}
                                        </p>
                                    </div>
                                    {session.notes && (
                                        <p className="text-sm text-slate-600">{session.notes}</p>
                                    )}
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                                        {session.status_label}
                                    </span>
                                    <span className="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">
                                        {session.progress_percent}%
                                    </span>
                                </div>
                            </div>

                            <div className="mt-4 grid gap-3 md:grid-cols-4">
                                <Metric label="Target siswa" value={session.student_count} small />
                                <Metric label="Sudah scan" value={session.scanned_count} small />
                                <Metric label="Belum scan" value={session.pending_count} small />
                                <Metric label="Terlambat" value={session.status_breakdown.late} small />
                            </div>

                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    className={`h-full rounded-full transition-all ${accentClass}`}
                                    style={{ width: `${session.progress_percent}%` }}
                                />
                            </div>

                            <div className="mt-5 grid gap-6 xl:grid-cols-2">
                                <div>
                                    <h5 className="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Sudah scan
                                    </h5>
                                    <div className="mt-3 space-y-2">
                                        {session.present_students.map((student) => (
                                            <StudentRow
                                                key={`present-${student.id}`}
                                                name={student.name}
                                                subtitle={[student.nis, student.status_label, formatDateTime(student.scanned_at)].filter(Boolean).join(' | ')}
                                                note={student.notes}
                                                tone="success"
                                            />
                                        ))}
                                        {session.present_students.length === 0 && (
                                            <EmptyState text="Belum ada siswa yang tercatat scan pada sesi ini." />
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <h5 className="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Belum scan
                                    </h5>
                                    <div className="mt-3 space-y-2">
                                        {session.pending_students.map((student) => (
                                            <StudentRow
                                                key={`pending-${student.id}`}
                                                name={student.name}
                                                subtitle={student.nis ? `NIS ${student.nis}` : 'NIS belum diisi'}
                                                tone="warning"
                                            />
                                        ))}
                                        {session.pending_students.length === 0 && (
                                            <EmptyState text="Semua siswa pada sesi ini sudah tercatat." />
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
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

function Metric({ label, value, small = false }) {
    return (
        <div className="rounded-2xl bg-white p-5 shadow-sm">
            <p className={`text-slate-500 ${small ? 'text-xs uppercase tracking-[0.16em]' : 'text-sm'}`}>
                {label}
            </p>
            <p className={`mt-2 font-semibold text-slate-900 ${small ? 'text-2xl' : 'text-3xl'}`}>
                {value}
            </p>
        </div>
    );
}

function StudentRow({ name, subtitle, note, tone }) {
    const toneClass = tone === 'success'
        ? 'border-emerald-200 bg-emerald-50'
        : 'border-amber-200 bg-amber-50';

    return (
        <div className={`rounded-2xl border p-4 ${toneClass}`}>
            <p className="font-medium text-slate-900">{name || '-'}</p>
            <p className="mt-1 text-sm text-slate-600">{subtitle}</p>
            {note && (
                <p className="mt-2 text-sm text-slate-500">{note}</p>
            )}
        </div>
    );
}

function EmptyState({ text }) {
    return (
        <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
            {text}
        </div>
    );
}

function formatDateTime(value) {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleString('id-ID');
}
