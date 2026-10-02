<?php

namespace Tests\Feature\Auth;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register/student');

        $response->assertStatus(200);
    }

    public function test_new_students_can_register_and_wait_for_approval(): void
    {
        $response = $this->post('/register/student', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'nis' => 'S-001',
            'nik' => '3170000000000001',
            'phone' => '08123456789',
            'gender' => 'male',
            'birth_date' => '2008-01-02',
            'address' => 'Jl. Siswa No. 1',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'student',
            'approval_status' => 'pending',
        ]);
        $this->assertDatabaseHas('student_profiles', [
            'nis' => 'S-001',
            'nik' => '3170000000000001',
        ]);
    }

    public function test_new_teachers_can_register_and_wait_for_approval(): void
    {
        $response = $this->post('/register/teacher', [
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'nip' => 'T-001',
            'nik' => '3170000000000002',
            'phone' => '08123456780',
            'gender' => 'female',
            'birth_date' => '1990-02-03',
            'address' => 'Jl. Guru No. 2',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.com',
            'role' => 'teacher',
            'approval_status' => 'pending',
        ]);
        $this->assertDatabaseHas('teacher_profiles', [
            'nip' => 'T-001',
            'nik' => '3170000000000002',
        ]);
    }

    public function test_student_qr_token_is_generated_after_admin_approval(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $student = User::create([
            'name' => 'Pending Student',
            'email' => 'pending-student@example.com',
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Pending,
            'password' => 'password',
        ]);

        $student->studentProfile()->create([
            'nis' => 'S-002',
            'nik' => '3170000000000003',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.approval.approve', $student));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'approval_status' => 'approved',
        ]);
        $this->assertDatabaseMissing('student_profiles', [
            'user_id' => $student->id,
            'qr_token' => null,
        ]);
    }
}
