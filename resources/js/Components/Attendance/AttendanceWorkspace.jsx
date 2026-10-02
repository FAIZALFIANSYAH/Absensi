import { Head, useForm } from '@inertiajs/react';
import QrScannerPanel from '@/Components/Attendance/QrScannerPanel';

const attendanceLabels = {
    present: 'Hadir',
    late: 'Terlambat',
    excused: 'Izin',
    absent: 'Absen',
};

export default function AttendanceWorkspace({
    layout: Layout,
    title,
    description,
    classes,
    sessions,
    defaultStatus,
    openStatus,
    todayDate,
    flashStatus,
    sessionRoute,
    closeRoute,
    scanRoute,
    submitClassName,
    closeClassName,
}) {
    const sessionForm = useForm({
        school_class_id: '',
        session_date: todayDate,
        start_time: '07:00',
        notes: '',
    });

    const scanForm = useForm({
        attendance_session_id: '',
        qr_token: '',
        status: defaultStatus,
        notes: '',
    });

    const closeForm = useForm({
        attendance_session_id: '',
    });

    const openSessions = sessions.filter((session) => session.status === openStatus);
    const activeStudents = openSessions.reduce((total, session) => total + session.student_count, 0);
    const activeScans = openSessions.reduce((total, session) => total + session.scanned_count, 0);

    async function submitScanToken(qrToken) {
        scanForm.setData('qr_token', qrToken);

        return new Promise((resolve) => {
            scanForm.transform((data) => ({
                ...data,
                qr_token: qrToken,
            }));

            scanForm.post(route(scanRoute), {
                preserveScroll: true,
                onSuccess: () => {
                    scanForm.reset('qr_token', 'notes');
                    scanForm.setData('status', defaultStatus);
                    resolve(true);
                },
                onFinish: () => {
                    scanForm.transform((data) => data);
                },
                onError: () => resolve(false),
            });
        });
    }

    return (
        <Layout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        {title}
                    </h2>
                    <p className="text-sm text-slate-600">{description}</p>
                </div>
            }
        >
            <Head title={title} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {flashStatus && (
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {flashStatus}
                        </div>
                    )}

                    <div className="grid gap-4 md:grid-cols-3">
                        <SummaryCard label="Sesi aktif" value={openSessions.length} />
                        <SummaryCard label="Scan aktif" value={activeScans} />
                        <SummaryCard label="Target siswa aktif" value={activeStudents} />
                    </div>

                    <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Buka sesi absensi
                            </h3>
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    sessionForm.post(route(sessionRoute));
                                }}
                                className="mt-4 space-y-4"
                            >
                                <Field label="Kelas" error={sessionForm.errors.school_class_id}>
                                    <select
                                        value={sessionForm.data.school_class_id}
                                        onChange={(e) => sessionForm.setData('school_class_id', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    >
                                        <option value="">Pilih kelas</option>
                                        {classes.map((schoolClass) => (
                                            <option key={schoolClass.id} value={schoolClass.id}>
                                                {schoolClass.name} ({schoolClass.student_count} siswa)
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <Field label="Tanggal sesi" error={sessionForm.errors.session_date}>
                                        <input
                                            type="date"
                                            value={sessionForm.data.session_date}
                                            onChange={(e) => sessionForm.setData('session_date', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                    </Field>
                                    <Field label="Jam mulai" error={sessionForm.errors.start_time}>
                                        <input
                                            type="time"
                                            value={sessionForm.data.start_time}
                                            onChange={(e) => sessionForm.setData('start_time', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                    </Field>
                                </div>
                                <Field label="Catatan" error={sessionForm.errors.notes}>
                                    <textarea
                                        value={sessionForm.data.notes}
                                        onChange={(e) => sessionForm.setData('notes', e.target.value)}
                                        rows="3"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <button
                                    className={submitClassName}
                                    disabled={sessionForm.processing}
                                >
                                    Buka Sesi
                                </button>
                            </form>
                        </div>

                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Rekam scan QR
                            </h3>
                            <div className="mt-4 space-y-4">
                                <Field label="Sesi aktif" error={scanForm.errors.attendance_session_id}>
                                    <select
                                        value={scanForm.data.attendance_session_id}
                                        onChange={(e) => scanForm.setData('attendance_session_id', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                        disabled={openSessions.length === 0}
                                    >
                                        <option value="">Pilih sesi</option>
                                        {openSessions.map((session) => (
                                            <option key={session.id} value={session.id}>
                                                {session.school_class.name} - {session.session_date} ({session.scanned_count}/{session.student_count})
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <QrScannerPanel
                                    disabled={scanForm.processing}
                                    hasActiveSession={Boolean(scanForm.data.attendance_session_id)}
                                    onScan={submitScanToken}
                                />

                                <form
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        submitScanToken(scanForm.data.qr_token);
                                    }}
                                    className="space-y-4 rounded-2xl border border-slate-200 p-4"
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div>
                                            <h4 className="text-base font-semibold text-slate-900">Fallback input manual</h4>
                                            <p className="mt-1 text-sm text-slate-600">
                                                Gunakan saat kamera gagal terbuka, device tidak mendukung, atau uji cepat di local.
                                            </p>
                                        </div>
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="QR token siswa" error={scanForm.errors.qr_token}>
                                            <input
                                                value={scanForm.data.qr_token}
                                                onChange={(e) => scanForm.setData('qr_token', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                required
                                                disabled={openSessions.length === 0}
                                            />
                                        </Field>
                                        <Field label="Status" error={scanForm.errors.status}>
                                            <select
                                                value={scanForm.data.status}
                                                onChange={(e) => scanForm.setData('status', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                disabled={openSessions.length === 0}
                                            >
                                                <option value="present">Present</option>
                                                <option value="late">Late</option>
                                                <option value="excused">Excused</option>
                                                <option value="absent">Absent</option>
                                            </select>
                                        </Field>
                                    </div>
                                    <Field label="Catatan scan" error={scanForm.errors.notes}>
                                        <textarea
                                            value={scanForm.data.notes}
                                            onChange={(e) => scanForm.setData('notes', e.target.value)}
                                            rows="3"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            disabled={openSessions.length === 0}
                                        />
                                    </Field>
                                    <button
                                        className={submitClassName}
                                        disabled={scanForm.processing || openSessions.length === 0}
                                    >
                                        Simpan Scan Manual
                                    </button>
                                </form>

                                {openSessions.length === 0 && (
                                    <p className="text-sm text-amber-600">
                                        Belum ada sesi aktif. Buka sesi lebih dulu sebelum scan.
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Riwayat sesi absensi</h3>
                        <div className="mt-4 space-y-4">
                            {sessions.map((session) => (
                                <div key={session.id} className="rounded-2xl border border-slate-200 p-4">
                                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div className="space-y-2">
                                            <div>
                                                <p className="font-medium text-slate-900">
                                                    {session.school_class.name}
                                                </p>
                                                <p className="text-sm text-slate-600">
                                                    {session.session_date} | Mulai {session.start_time}
                                                    {session.end_time ? ` | Selesai ${session.end_time}` : ''}
                                                    {' '}| Dibuka oleh {session.opener}
                                                </p>
                                            </div>
                                            {session.notes && (
                                                <p className="text-sm text-slate-600">{session.notes}</p>
                                            )}
                                            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                                <MiniMetric
                                                    label="Progress"
                                                    value={`${session.scanned_count}/${session.student_count}`}
                                                />
                                                <MiniMetric
                                                    label="Persentase"
                                                    value={`${session.progress_percent}%`}
                                                />
                                                <MiniMetric
                                                    label="Tersisa"
                                                    value={session.remaining_count}
                                                />
                                                <MiniMetric
                                                    label="Terlambat"
                                                    value={session.status_breakdown.late}
                                                />
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">
                                                {session.status}
                                            </span>
                                            {session.can_close && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        closeForm.post(route(closeRoute), {
                                                            data: { attendance_session_id: session.id },
                                                        })
                                                    }
                                                    className={closeClassName}
                                                    disabled={closeForm.processing}
                                                >
                                                    Tutup Sesi
                                                </button>
                                            )}
                                        </div>
                                    </div>

                                    <div className="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                                        <MiniMetric label="Hadir" value={session.status_breakdown.present} />
                                        <MiniMetric label="Izin" value={session.status_breakdown.excused} />
                                        <MiniMetric label="Absen" value={session.status_breakdown.absent} />
                                        <MiniMetric label="Total Scan" value={session.scanned_count} />
                                    </div>

                                    <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            className="h-full rounded-full bg-emerald-500 transition-all"
                                            style={{ width: `${session.progress_percent}%` }}
                                        />
                                    </div>

                                    <div className="mt-4 grid gap-2 md:grid-cols-2">
                                        {session.records.map((record) => (
                                            <div key={record.id} className="rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                                                <p className="font-medium text-slate-900">
                                                    {record.student_name ?? 'Siswa tidak diketahui'}
                                                </p>
                                                <p className="mt-1 text-slate-500">
                                                    ID akun: {record.student_id}
                                                    {record.student_nis ? ` | NIS: ${record.student_nis}` : ''}
                                                    {record.student_email ? ` | ${record.student_email}` : ''}
                                                </p>
                                                <p className="mt-1">
                                                    {attendanceLabels[record.status] ?? record.status} | {new Date(record.scanned_at).toLocaleString('id-ID')}
                                                </p>
                                                {record.notes && (
                                                    <p className="mt-1 text-slate-500">{record.notes}</p>
                                                )}
                                            </div>
                                        ))}
                                        {session.records.length === 0 && (
                                            <p className="text-sm text-slate-500">Belum ada scan pada sesi ini.</p>
                                        )}
                                    </div>
                                </div>
                            ))}
                            {sessions.length === 0 && (
                                <p className="text-sm text-slate-500">Belum ada sesi absensi yang dibuat.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}

function SummaryCard({ label, value }) {
    return (
        <div className="rounded-2xl bg-white p-5 shadow-sm">
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-semibold text-slate-900">{value}</p>
        </div>
    );
}

function MiniMetric({ label, value }) {
    return (
        <div className="rounded-xl bg-slate-50 px-3 py-2">
            <p className="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">
                {label}
            </p>
            <p className="mt-1 text-sm font-semibold text-slate-900">{value}</p>
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
