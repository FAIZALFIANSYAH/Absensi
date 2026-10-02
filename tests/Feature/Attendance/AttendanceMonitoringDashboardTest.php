<?php

namespace Tests\Feature\Attendance;

use App\Enums\ApprovalStatus;
use App\Enums\SessionStatus;
use App\Enums\UserRole;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AttendanceMonitoringDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_includes_monitoring_for_owned_class(): void
    {
        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $student = $this->createStudent('S-301', 'qr-student-301');

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Teacher/Dashboard/Index')
                ->where('monitoring.overview.session_count', 1)
                ->where('monitoring.overview.open_session_count', 1)
                ->where('monitoring.sessions.0.pending_count', 1)
                ->where('monitoring.sessions.0.present_students', [])
            );
    }

    public function test_teacher_dashboard_filter_can_limit_to_selected_date(): void
    {
        [$teacher, $schoolClass] = $this->createTeacherAndClass();

        AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-12',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Closed,
            'end_time' => '07:45:00',
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard', ['session_date' => '2026-05-12']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Teacher/Dashboard/Index')
                ->where('monitoring.filters.session_date', '2026-05-12')
                ->where('monitoring.overview.session_count', 1)
                ->where('monitoring.sessions.0.session_date', '2026-05-12')
                ->where('monitoring.sessions.0.status', SessionStatus::Closed->value)
            );
    }

    public function test_admin_dashboard_monitoring_can_show_scanned_and_pending_students(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XII IPA 7',
            'slug' => 'xii-ipa-7-2026-2027',
            'grade_level' => 'XII',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $studentOne = $this->createStudent('S-302', 'qr-student-302');
        $studentTwo = $this->createStudent('S-303', 'qr-student-303');

        foreach ([$studentOne, $studentTwo] as $student) {
            $student->schoolClasses()->attach($schoolClass->id, [
                'member_role' => 'student',
                'joined_at' => now(),
            ]);
        }

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $session->records()->create([
            'student_id' => $studentOne->id,
            'scanned_by' => $admin->id,
            'scanned_at' => now(),
            'status' => 'present',
            'scan_payload' => ['qr_token' => 'qr-student-302'],
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard/Index')
                ->where('monitoring.overview.scanned_student_count', 1)
                ->where('monitoring.overview.pending_student_count', 1)
                ->where('monitoring.sessions.0.present_students.0.name', $studentOne->name)
                ->where('monitoring.sessions.0.pending_students.0.name', $studentTwo->name)
            );
    }

    private function createTeacherAndClass(): array
    {
        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XI IPA Monitoring',
            'slug' => 'xi-ipa-monitoring-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        return [$teacher, $schoolClass];
    }

    private function createStudent(string $nis, string $qrToken): User
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => $nis,
            'nik' => str_pad((string) random_int(1, 9999999999999999), 16, '0', STR_PAD_LEFT),
            'qr_token' => $qrToken,
            'qr_generated_at' => now(),
        ]);

        return $student;
    }
}
