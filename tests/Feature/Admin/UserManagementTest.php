<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_student_user_from_user_management(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Student Manual',
                'email' => 'student.manual@example.com',
                'role' => 'student',
                'approval_status' => 'approved',
                'password' => 'password',
                'password_confirmation' => 'password',
                'nis' => 'S-900',
                'nik' => '3170000000000900',
                'phone' => '08123456789',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'student.manual@example.com',
            'role' => 'student',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'student.manual@example.com')->firstOrFail();

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $createdUser->id,
            'nis' => 'S-900',
        ]);
    }

    public function test_superadmin_can_filter_users_by_role(): void
    {
        $admin = $this->createAdmin();
        $teacher = User::factory()->create([
            'name' => 'Teacher Filter',
            'email' => 'teacher.filter@example.com',
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);
        $student = User::factory()->create([
            'name' => 'Student Filter',
            'email' => 'student.filter@example.com',
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher->teacherProfile()->create([
            'nip' => 'T-001',
            'nik' => '3170000000000001',
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-001',
            'nik' => '3170000000000002',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'role' => 'teacher',
        ]));

        $response->assertOk();
        $response->assertSee('Teacher Filter');
        $response->assertDontSee('Student Filter');
    }

    public function test_superadmin_can_send_reset_password_link_to_user(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher->teacherProfile()->create([
            'nip' => 'T-002',
            'nik' => '3170000000000003',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.password.reset-link', $teacher))
            ->assertRedirect();

        Notification::assertSentTo($teacher, ResetPassword::class);
    }

    public function test_superadmin_can_deactivate_and_reactivate_user(): void
    {
        $admin = $this->createAdmin();
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
            'is_active' => true,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-003',
            'nik' => '3170000000000004',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $student))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.activate', $student))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_not_delete_user_with_operational_relations(): void
    {
        $admin = $this->createAdmin();
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher->teacherProfile()->create([
            'nip' => 'T-099',
            'nik' => '3170000000000099',
        ]);

        Assignment::create([
            'school_class_id' => SchoolClass::create([
                'name' => 'XI IPA 9',
                'slug' => 'xi-ipa-9-2026-2027',
                'grade_level' => 'XI',
                'major' => 'IPA',
                'academic_year' => '2026/2027',
                'created_by' => $admin->id,
            ])->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Operasional',
            'description' => 'Masih dipakai oleh sistem.',
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $teacher))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
        ]);
    }

    public function test_superadmin_can_not_delete_user_that_is_referenced_as_grader_or_admin_actor(): void
    {
        $admin = $this->createAdmin();
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $teacher->teacherProfile()->create([
            'nip' => 'T-100',
            'nik' => '3170000000000100',
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-100',
            'nik' => '3170000000000101',
        ]);

        $class = SchoolClass::create([
            'name' => 'XII IPA 2',
            'slug' => 'xii-ipa-2-2026-2027',
            'grade_level' => 'XII',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $assignment = Assignment::create([
            'school_class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Grading',
            'description' => 'Tugas untuk menguji grader.',
            'due_at' => now()->addDay(),
        ]);

        Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => 'submissions/demo.pdf',
            'original_name' => 'demo.pdf',
            'status' => 'graded',
            'submitted_at' => now(),
            'graded_at' => now(),
            'graded_by' => $teacher->id,
            'score' => 90,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $teacher))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertStatus(403);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
            'is_active' => true,
        ]);
    }
}
