import AttendanceWorkspace from '@/Components/Attendance/AttendanceWorkspace';
import AdminLayout from '@/Layouts/AdminLayout';

export default function AdminAttendance(props) {
    return (
        <AttendanceWorkspace
            layout={AdminLayout}
            title="Attendance Admin"
            description="Superadmin dapat membuka, menutup, dan merekam scan QR siswa."
            {...props}
            sessionRoute="admin.attendance.sessions.store"
            closeRoute="admin.attendance.sessions.close"
            scanRoute="admin.attendance.scan"
            submitClassName="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white"
            closeClassName="rounded-full border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700"
        />
    );
}
