<?php

namespace Database\Seeders;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        User::query()->updateOrCreate(
            ['email' => 'admin@absensi.test'],
            [
                'name' => 'Superadmin',
                'role' => UserRole::Superadmin,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        // Teacher
        $teacher = User::query()->updateOrCreate(
            ['email' => 'teacher@absensi.test'],
            [
                'name' => 'Teacher',
                'role' => UserRole::Teacher,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $this->seedTeacherProfile($teacher, [
            'nip' => 'T-001',
            'nik' => '3170000000000001',
            'phone' => '081200000001',
            'gender' => 'male',
            'birth_date' => '1990-01-10',
            'address' => 'Jl. Guru 1, Kota Demo',
        ]);

        $teacher2 = User::query()->updateOrCreate(
            ['email' => 'teacher2@absensi.test'],
            [
                'name' => 'Teacher 2',
                'role' => UserRole::Teacher,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $this->seedTeacherProfile($teacher2, [
            'nip' => 'T-002',
            'nik' => '3170000000000002',
            'phone' => '081200000002',
            'gender' => 'female',
            'birth_date' => '1991-02-11',
            'address' => 'Jl. Guru 2, Kota Demo',
        ]);

        $teacher3 = User::query()->updateOrCreate(
            ['email' => 'teacher3@absensi.test'],
            [
                'name' => 'Teacher 3',
                'role' => UserRole::Teacher,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $this->seedTeacherProfile($teacher3, [
            'nip' => 'T-003',
            'nik' => '3170000000000003',
            'phone' => '081200000003',
            'gender' => 'male',
            'birth_date' => '1992-03-12',
            'address' => 'Jl. Guru 3, Kota Demo',
        ]);

        // Student
        $student = User::query()->updateOrCreate(
            ['email' => 'student@absensi.test'],
            [
                'name' => 'Student 1',
                'role' => UserRole::Student,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $this->seedStudentProfile($student, [
            'nis' => 'S-001',
            'nik' => '3170000000000101',
            'phone' => '081300000001',
            'gender' => 'male',
            'birth_date' => '2008-01-15',
            'address' => 'Jl. Siswa 1, Kota Demo',
        ]);

        $student2 = User::query()->updateOrCreate(
            ['email' => 'student2@absensi.test'],
            [
                'name' => 'Student 2',
                'role' => UserRole::Student,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $this->seedStudentProfile($student2, [
            'nis' => 'S-002',
            'nik' => '3170000000000102',
            'phone' => '081300000002',
            'gender' => 'female',
            'birth_date' => '2008-02-16',
            'address' => 'Jl. Siswa 2, Kota Demo',
        ]);

        $student3 = User::query()->updateOrCreate(
            ['email' => 'student3@absensi.test'],
            [
                'name' => 'Student 3',
                'role' => UserRole::Student,
                'approval_status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $this->seedStudentProfile($student3, [
            'nis' => 'S-003',
            'nik' => '3170000000000103',
            'phone' => '081300000003',
            'gender' => 'male',
            'birth_date' => '2008-03-17',
            'address' => 'Jl. Siswa 3, Kota Demo',
        ]);
    }

    private function seedTeacherProfile(User $user, array $attributes): void
    {
        $user->teacherProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $attributes,
        );
    }

    private function seedStudentProfile(User $user, array $attributes): void
    {
        /** @var StudentProfile $profile */
        $profile = $user->studentProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $attributes,
        );

        $profile->ensureQrToken();
    }
}
