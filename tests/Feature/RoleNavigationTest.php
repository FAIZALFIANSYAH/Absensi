<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_open_teacher_classes_page(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.classes.index'))
            ->assertOk();
    }

    public function test_student_can_open_student_navigation_pages(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-010',
            'nik' => '3170000000000010',
        ]);

        $this->actingAs($student)
            ->get(route('student.classes.index'))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('student.qr.index'))
            ->assertOk();
    }
}
