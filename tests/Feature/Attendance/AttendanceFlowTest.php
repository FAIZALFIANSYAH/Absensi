<?php

namespace Tests\Feature\Attendance;

use App\Enums\ApprovalStatus;
use App\Enums\SessionStatus;
use App\Enums\UserRole;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_open_attendance_session_for_owned_class(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'X IPA 3',
            'slug' => 'x-ipa-3-2026-2027',
            'grade_level' => 'X',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.attendance.sessions.store'), [
            'school_class_id' => $schoolClass->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00',
            'notes' => 'Sesi pagi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_sessions', [
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'status' => 'open',
        ]);
    }

    public function test_teacher_can_record_student_scan_for_open_session(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-200',
            'nik' => '3170000000000200',
            'qr_token' => 'qr-student-200',
            'qr_generated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XI IPA 1',
            'slug' => 'xi-ipa-1-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => 'open',
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.attendance.scan'), [
            'attendance_session_id' => $session->id,
            'qr_token' => 'qr-student-200',
            'status' => 'present',
            'notes' => 'Masuk tepat waktu',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'scanned_by' => $teacher->id,
            'status' => 'present',
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'scan_payload->student_name' => $student->name,
            'scan_payload->student_email' => $student->email,
            'scan_payload->nis' => 'S-200',
        ]);
    }

    public function test_teacher_can_record_student_scan_from_json_qr_payload(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-200A',
            'nik' => '3170000000001200',
            'qr_token' => 'qr-student-json-200',
            'qr_generated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XI IPA JSON',
            'slug' => 'xi-ipa-json-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => 'open',
        ]);

        $jsonPayload = json_encode([
            'v' => 1,
            'token' => 'qr-student-json-200',
            'issued_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($teacher)->post(route('teacher.attendance.scan'), [
            'attendance_session_id' => $session->id,
            'qr_token' => $jsonPayload,
            'status' => 'present',
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'scanned_by' => $teacher->id,
            'status' => 'present',
        ]);
    }

    public function test_duplicate_scan_is_rejected(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-201',
            'nik' => '3170000000000201',
            'qr_token' => 'qr-student-201',
            'qr_generated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XI IPA 2',
            'slug' => 'xi-ipa-2-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => 'open',
        ]);

        $payload = [
            'attendance_session_id' => $session->id,
            'qr_token' => 'qr-student-201',
            'status' => 'present',
        ];

        $this->actingAs($teacher)->post(route('teacher.attendance.scan'), $payload)->assertRedirect();

        $this->actingAs($teacher)
            ->from(route('teacher.attendance.index'))
            ->post(route('teacher.attendance.scan'), $payload)
            ->assertSessionHasErrors('qr_token');
    }

    public function test_teacher_can_close_open_session(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XII IPA 1',
            'slug' => 'xii-ipa-1-2026-2027',
            'grade_level' => 'XII',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $this->actingAs($teacher)->post(route('teacher.attendance.sessions.close'), [
            'attendance_session_id' => $session->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'status' => 'closed',
        ]);
    }

    public function test_can_not_open_duplicate_open_session_for_same_class_and_date(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XII IPS 1',
            'slug' => 'xii-ips-1-2026-2027',
            'grade_level' => 'XII',
            'major' => 'IPS',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $this->actingAs($teacher)
            ->from(route('teacher.attendance.index'))
            ->post(route('teacher.attendance.sessions.store'), [
                'school_class_id' => $schoolClass->id,
                'session_date' => '2026-05-13',
                'start_time' => '07:15',
            ])
            ->assertSessionHasErrors('school_class_id');
    }

    public function test_scan_is_rejected_when_session_has_been_closed(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-202',
            'nik' => '3170000000000202',
            'qr_token' => 'qr-student-202',
            'qr_generated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'X IPA 4',
            'slug' => 'x-ipa-4-2026-2027',
            'grade_level' => 'X',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'end_time' => '07:30:00',
            'status' => SessionStatus::Closed,
        ]);

        $this->actingAs($teacher)
            ->from(route('teacher.attendance.index'))
            ->post(route('teacher.attendance.scan'), [
                'attendance_session_id' => $session->id,
                'qr_token' => 'qr-student-202',
                'status' => 'present',
            ])
            ->assertSessionHasErrors('attendance_session_id');
    }

    public function test_scan_is_rejected_for_student_outside_the_session_class(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-203',
            'nik' => '3170000000000203',
            'qr_token' => 'qr-student-203',
            'qr_generated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'X IPS 1',
            'slug' => 'x-ips-1-2026-2027',
            'grade_level' => 'X',
            'major' => 'IPS',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $this->actingAs($teacher)
            ->from(route('teacher.attendance.index'))
            ->post(route('teacher.attendance.scan'), [
                'attendance_session_id' => $session->id,
                'qr_token' => 'qr-student-203',
                'status' => 'present',
            ])
            ->assertSessionHasErrors('qr_token');
    }

    public function test_scan_is_rejected_for_student_with_pending_account(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Pending,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-204',
            'nik' => '3170000000000204',
            'qr_token' => 'qr-student-204',
            'qr_generated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XI IPS 2',
            'slug' => 'xi-ips-2-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPS',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $this->actingAs($teacher)
            ->from(route('teacher.attendance.index'))
            ->post(route('teacher.attendance.scan'), [
                'attendance_session_id' => $session->id,
                'qr_token' => 'qr-student-204',
                'status' => 'present',
            ])
            ->assertSessionHasErrors('qr_token');
    }

    public function test_teacher_can_not_open_attendance_for_unowned_class(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XII IPA 2',
            'slug' => 'xii-ipa-2-2026-2027',
            'grade_level' => 'XII',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.sessions.store'), [
                'school_class_id' => $schoolClass->id,
                'session_date' => '2026-05-13',
                'start_time' => '07:00',
            ])
            ->assertNotFound();
    }

    public function test_admin_can_record_student_scan_for_any_class(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-205',
            'nik' => '3170000000000205',
            'qr_token' => 'qr-student-205',
            'qr_generated_at' => now(),
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'XII IPS 2',
            'slug' => 'xii-ips-2-2026-2027',
            'grade_level' => 'XII',
            'major' => 'IPS',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $teacher->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'teacher',
            'joined_at' => now(),
        ]);

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_class_id' => $schoolClass->id,
            'opened_by' => $teacher->id,
            'session_date' => '2026-05-13',
            'start_time' => '07:00:00',
            'status' => SessionStatus::Open,
        ]);

        $this->actingAs($admin)->post(route('admin.attendance.scan'), [
            'attendance_session_id' => $session->id,
            'qr_token' => 'qr-student-205',
            'status' => 'late',
            'notes' => 'Dipindai admin',
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'scanned_by' => $admin->id,
            'status' => 'late',
        ]);
    }
}
