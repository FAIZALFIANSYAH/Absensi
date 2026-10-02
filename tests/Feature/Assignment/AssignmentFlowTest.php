<?php

namespace Tests\Feature\Assignment;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_publish_assignment_for_owned_class(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();

        $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
            'school_class_id' => $schoolClass->id,
            'title' => 'Tugas Aljabar',
            'description' => 'Kerjakan soal halaman 1-3.',
            'deadline_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'file' => UploadedFile::fake()->create('tugas.pdf', 150, 'application/pdf'),
        ])->assertRedirect();

        $assignment = Assignment::first();

        $this->assertNotNull($assignment);
        $this->assertDatabaseHas('assignments', [
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Aljabar',
            'status' => 'published',
        ]);
        Storage::disk('local')->assertExists($assignment->file_path);
    }

    public function test_student_can_submit_assignment_from_owned_class(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $student = $this->createStudent();

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $assignment = Assignment::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Geometri',
            'description' => 'Kumpulkan sebelum besok.',
            'deadline_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $this->actingAs($student)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('jawaban.pdf', 120, 'application/pdf'),
            'notes' => 'Sudah saya kerjakan.',
        ])->assertRedirect();

        $submission = Submission::first();

        $this->assertNotNull($submission);
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => 'submitted',
        ]);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    public function test_student_cannot_submit_assignment_twice(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $student = $this->createStudent();

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $assignment = Assignment::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Fisika',
            'deadline_at' => now()->addDay(),
            'status' => 'published',
        ]);

        Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => 'submissions/'.$assignment->id.'/existing.pdf',
            'file_name' => 'existing.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($student)
            ->from(route('student.assignments.index'))
            ->post(route('student.assignments.submit', $assignment), [
                'file' => UploadedFile::fake()->create('jawaban-baru.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasErrors('assignment_id');
    }

    public function test_student_submission_is_marked_late_when_deadline_has_passed(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $student = $this->createStudent();

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $assignment = Assignment::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Kimia',
            'deadline_at' => now()->subHour(),
            'status' => 'published',
        ]);

        $this->actingAs($student)->post(route('student.assignments.submit', $assignment), [
            'file' => UploadedFile::fake()->create('jawaban-kimia.pdf', 120, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => 'late',
        ]);
    }

    public function test_student_cannot_download_other_student_submission_even_in_same_class(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $owner = $this->createStudent();
        $classmate = $this->createStudent();

        $owner->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $classmate->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $assignment = Assignment::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Tugas Biologi',
            'deadline_at' => now()->addDay(),
            'status' => 'published',
        ]);

        Storage::disk('local')->put('submissions/'.$assignment->id.'/jawaban.pdf', 'dummy content');

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $owner->id,
            'file_path' => 'submissions/'.$assignment->id.'/jawaban.pdf',
            'file_name' => 'jawaban.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($classmate)
            ->get(route('student.submissions.download', $submission))
            ->assertForbidden();
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
            'name' => 'X Assignment',
            'slug' => 'x-assignment-2026-2027',
            'grade_level' => 'X',
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

    private function createStudent(): User
    {
        return User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);
    }
}
