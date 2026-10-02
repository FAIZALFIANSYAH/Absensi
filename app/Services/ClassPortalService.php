<?php

namespace App\Services;

use App\Enums\ClassMemberRole;
use App\Models\ClassMember;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Collection;

class ClassPortalService
{
    public function teacherIndexFor(User $user): Collection
    {
        return $this->classQueryForUser($user)
            ->get()
            ->map(fn (SchoolClass $schoolClass) => $this->mapIndexClass($schoolClass, $user))
            ->values();
    }

    public function studentIndexFor(User $user): Collection
    {
        return $this->classQueryForUser($user)
            ->get()
            ->map(fn (SchoolClass $schoolClass) => $this->mapIndexClass($schoolClass, $user))
            ->values();
    }

    public function teacherPortal(SchoolClass $schoolClass): array
    {
        $schoolClass->loadMissing($this->relations());

        $teachers = $this->mapTeacherMembers($schoolClass)->values();
        $subjectTeachers = $this->mapSubjectTeachers($schoolClass)->values();
        $students = $this->mapStudents($schoolClass)->values();

        return [
            'school_class' => $this->mapClassInfo($schoolClass),
            'metrics' => [
                'teacher_count' => $teachers->count(),
                'subject_teacher_count' => $subjectTeachers->count(),
                'student_count' => $students->count(),
            ],
            'teachers' => $teachers,
            'subject_teachers' => $subjectTeachers,
            'students' => $students,
        ];
    }

    public function studentPortal(SchoolClass $schoolClass, User $student): array
    {
        $schoolClass->loadMissing($this->relations());

        $subjectTeachers = $this->mapSubjectTeachers($schoolClass)->values();
        $students = $this->mapStudents($schoolClass);
        $classmates = $students
            ->reject(fn (array $classmate) => $classmate['id'] === $student->id)
            ->values();

        return [
            'school_class' => $this->mapClassInfo($schoolClass),
            'metrics' => [
                'subject_teacher_count' => $subjectTeachers->count(),
                'student_count' => $students->count(),
                'classmate_count' => $classmates->count(),
            ],
            'subject_teachers' => $subjectTeachers,
            'classmates' => $classmates,
        ];
    }

    private function classQueryForUser(User $user)
    {
        return SchoolClass::query()
            ->whereHas('members', fn ($query) => $query->where('user_id', $user->id))
            ->with($this->relations())
            ->orderBy('name');
    }

    private function relations(): array
    {
        return [
            'homeroomTeacher:id,name,email',
            'members.user:id,name,email',
            'subjectTeachers.teacher:id,name,email',
        ];
    }

    private function mapIndexClass(SchoolClass $schoolClass, User $user): array
    {
        $teacherCount = $this->mapTeacherMembers($schoolClass)->count();
        $studentCount = $this->mapStudents($schoolClass)->count();
        $memberRoles = $schoolClass->members
            ->where('user_id', $user->id)
            ->map(fn (ClassMember $member) => $this->roleLabel($member->member_role))
            ->unique()
            ->values();

        return [
            'id' => $schoolClass->id,
            'name' => $schoolClass->name,
            'slug' => $schoolClass->slug,
            'grade_level' => $schoolClass->grade_level,
            'major' => $schoolClass->major,
            'academic_year' => $schoolClass->academic_year,
            'homeroom_teacher' => $schoolClass->homeroomTeacher ? [
                'id' => $schoolClass->homeroomTeacher->id,
                'name' => $schoolClass->homeroomTeacher->name,
                'email' => $schoolClass->homeroomTeacher->email,
            ] : null,
            'member_roles' => $memberRoles,
            'teacher_count' => $teacherCount,
            'subject_teacher_count' => $schoolClass->subjectTeachers->count(),
            'student_count' => $studentCount,
        ];
    }

    private function mapClassInfo(SchoolClass $schoolClass): array
    {
        return [
            'id' => $schoolClass->id,
            'name' => $schoolClass->name,
            'slug' => $schoolClass->slug,
            'grade_level' => $schoolClass->grade_level,
            'major' => $schoolClass->major,
            'academic_year' => $schoolClass->academic_year,
            'homeroom_teacher' => $schoolClass->homeroomTeacher ? [
                'id' => $schoolClass->homeroomTeacher->id,
                'name' => $schoolClass->homeroomTeacher->name,
                'email' => $schoolClass->homeroomTeacher->email,
            ] : null,
        ];
    }

    private function mapTeacherMembers(SchoolClass $schoolClass): Collection
    {
        return $schoolClass->members
            ->whereIn('member_role', [ClassMemberRole::Teacher, ClassMemberRole::HomeroomTeacher])
            ->groupBy('user_id')
            ->map(function (Collection $members) {
                /** @var ClassMember $firstMember */
                $firstMember = $members->first();
                $roles = $members
                    ->map(fn (ClassMember $member) => $member->member_role)
                    ->unique()
                    ->values();

                return [
                    'id' => $firstMember->user_id,
                    'name' => $firstMember->user?->name,
                    'email' => $firstMember->user?->email,
                    'roles' => $roles->map(fn (ClassMemberRole $role) => $role->value)->all(),
                    'role_labels' => $roles->map(fn (ClassMemberRole $role) => $this->roleLabel($role))->all(),
                ];
            })
            ->sortBy('name');
    }

    private function mapSubjectTeachers(SchoolClass $schoolClass): Collection
    {
        return $schoolClass->subjectTeachers
            ->sortBy('subject_name')
            ->map(fn (ClassSubjectTeacher $assignment) => [
                'id' => $assignment->id,
                'subject_name' => $assignment->subject_name,
                'teacher' => $assignment->teacher ? [
                    'id' => $assignment->teacher->id,
                    'name' => $assignment->teacher->name,
                    'email' => $assignment->teacher->email,
                ] : null,
            ]);
    }

    private function mapStudents(SchoolClass $schoolClass): Collection
    {
        return $schoolClass->members
            ->where('member_role', ClassMemberRole::Student)
            ->map(fn (ClassMember $member) => [
                'id' => $member->user_id,
                'name' => $member->user?->name,
                'email' => $member->user?->email,
            ])
            ->sortBy('name')
            ->values();
    }

    private function roleLabel(ClassMemberRole $role): string
    {
        return match ($role) {
            ClassMemberRole::Teacher => 'Guru Umum',
            ClassMemberRole::Student => 'Siswa',
            ClassMemberRole::HomeroomTeacher => 'Wali Kelas',
        };
    }
}
