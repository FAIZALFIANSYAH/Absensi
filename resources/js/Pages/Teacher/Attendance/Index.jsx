import AttendanceWorkspace from '@/Components/Attendance/AttendanceWorkspace';
import TeacherLayout from '@/Layouts/TeacherLayout';

export default function TeacherAttendance(props) {
    return (
        <AttendanceWorkspace
            layout={TeacherLayout}
            title="Attendance Guru"
            description="Guru dapat membuka, menutup, dan merekam scan QR siswa di kelas yang diampu."
            {...props}
            sessionRoute="teacher.attendance.sessions.store"
            closeRoute="teacher.attendance.sessions.close"
            scanRoute="teacher.attendance.scan"
            submitClassName="rounded-full bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
            closeClassName="rounded-full border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700"
        />
    );
}
