<?php

namespace Tests\Feature\Material;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_upload_material_for_owned_class(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();

        $response = $this->actingAs($teacher)->post(route('teacher.materials.store'), [
            'school_class_id' => $schoolClass->id,
            'title' => 'Bab 1 Sistem Persamaan',
            'description' => 'Materi pengantar untuk kelas.',
            'file' => UploadedFile::fake()->create('bab-1.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect();

        $material = Material::first();

        $this->assertNotNull($material);
        $this->assertDatabaseHas('materials', [
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Bab 1 Sistem Persamaan',
        ]);
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_teacher_cannot_upload_material_for_unowned_class(): void
    {
        Storage::fake('local');

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'approval_status' => ApprovalStatus::Approved,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'X IPA 9',
            'slug' => 'x-ipa-9-2026-2027',
            'grade_level' => 'X',
            'major' => 'IPA',
            'academic_year' => '2026/2027',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($teacher)->post(route('teacher.materials.store'), [
            'school_class_id' => $schoolClass->id,
            'title' => 'Bab 2',
            'file' => UploadedFile::fake()->create('bab-2.pdf', 100, 'application/pdf'),
        ])->assertNotFound();
    }

    public function test_student_can_view_only_materials_from_owned_classes(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $student = $this->createStudent();
        $otherStudent = $this->createStudent();

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        $otherClass = SchoolClass::create([
            'name' => 'XI IPS 7',
            'slug' => 'xi-ips-7-2026-2027',
            'grade_level' => 'XI',
            'major' => 'IPS',
            'academic_year' => '2026/2027',
            'created_by' => User::where('role', UserRole::Superadmin)->first()->id,
        ]);

        Material::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Materi Kelas Saya',
            'file_path' => 'materials/1/materi-saya.pdf',
            'file_name' => 'materi-saya.pdf',
            'file_type' => 'application/pdf',
            'published_at' => now(),
        ]);

        Material::create([
            'school_class_id' => $otherClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Materi Kelas Lain',
            'file_path' => 'materials/2/materi-lain.pdf',
            'file_name' => 'materi-lain.pdf',
            'file_type' => 'application/pdf',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('student.materials.index'));
        $response->assertOk();
        $response->assertSee('Materi Kelas Saya');
        $response->assertDontSee('Materi Kelas Lain');
    }

    public function test_student_can_download_material_only_from_owned_class(): void
    {
        Storage::fake('local');

        [$teacher, $schoolClass] = $this->createTeacherAndClass();
        $student = $this->createStudent();
        $stranger = $this->createStudent();

        $student->schoolClasses()->attach($schoolClass->id, [
            'member_role' => 'student',
            'joined_at' => now(),
        ]);

        Storage::disk('local')->put('materials/'.$schoolClass->id.'/materi.pdf', 'dummy content');

        $material = Material::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => 'Materi Download',
            'file_path' => 'materials/'.$schoolClass->id.'/materi.pdf',
            'file_name' => 'materi.pdf',
            'file_type' => 'application/pdf',
            'published_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('student.materials.download', $material))
            ->assertOk();

        $this->actingAs($stranger)
            ->get(route('student.materials.download', $material))
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
            'name' => 'XI IPA 5',
            'slug' => 'xi-ipa-5-2026-2027',
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

    private function createStudent(): User
    {
        return User::factory()->create([
            'role' => UserRole::Student,
            'approval_status' => ApprovalStatus::Approved,
        ]);
    }
}
