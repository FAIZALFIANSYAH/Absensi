<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_school_class(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => 'X IPA 1',
            'grade_level' => 'X',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('school_classes', [
            'name' => 'X IPA 1',
            'grade_level' => 'X',
        ]);
        $this->assertDatabaseHas('class_members', [
            'user_id' => $teacher->id,
            'member_role' => 'homeroom_teacher',
        ]);
    }

    public function test_superadmin_can_assign_teacher_and_enroll_student(): void
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

        $schoolClass = \App\Models\SchoolClass::create([
            'name' => 'XI IPS 2',
            'slug' => 'xi-ips-2-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPS',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.classes.teachers.store', $schoolClass), [
            'user_id' => $teacher->id,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.classes.students.store', $schoolClass), [
            'user_id' => $student->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('class_members', [
            'school_class_id' => $schoolClass->id,
            'user_id' => $teacher->id,
            'member_role' => 'teacher',
        ]);

        $this->assertDatabaseHas('class_members', [
            'school_class_id' => $schoolClass->id,
            'user_id' => $student->id,
            'member_role' => 'student',
        ]);
    }
}
