<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Class\AssignTeacherToClass;
use App\Actions\Class\AssignSubjectTeacher;
use App\Actions\Class\CreateSchoolClass;
use App\Actions\Class\EnrollStudentToClass;
use App\Actions\Class\RemoveStudentFromClass;
use App\Actions\Class\RemoveSubjectTeacher;
use App\Actions\Class\RemoveTeacherFromClass;
use App\Actions\Class\UpdateSubjectTeacher;
use App\Enums\ApprovalStatus;
use App\Enums\ClassMemberRole;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Class\AssignTeacherRequest;
use App\Http\Requests\Class\EnrollStudentRequest;
use App\Http\Requests\Class\StoreClassRequest;
use App\Http\Requests\Class\StoreSubjectTeacherRequest;
use App\Http\Requests\Class\UpdateSubjectTeacherRequest;
use App\Models\ClassMember;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClassController extends Controller
{
    public function __construct(
        private readonly CreateSchoolClass $createSchoolClass,
        private readonly AssignTeacherToClass $assignTeacherToClass,
        private readonly EnrollStudentToClass $enrollStudentToClass,
        private readonly AssignSubjectTeacher $assignSubjectTeacher,
        private readonly UpdateSubjectTeacher $updateSubjectTeacher,
        private readonly RemoveSubjectTeacher $removeSubjectTeacher,
        private readonly RemoveTeacherFromClass $removeTeacherFromClass,
        private readonly RemoveStudentFromClass $removeStudentFromClass,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', SchoolClass::class);

        return Inertia::render('Admin/Classes/Index', [
            'classes' => SchoolClass::query()
                ->with([
                    'homeroomTeacher:id,name,email',
                    'members.user:id,name,email',
                    'subjectTeachers:id,school_class_id',
                ])
                ->latest()
                ->get()
                ->map(fn (SchoolClass $schoolClass) => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'slug' => $schoolClass->slug,
                    'grade_level' => $schoolClass->grade_level,
                    'major' => $schoolClass->major,
                    'academic_year' => $schoolClass->academic_year,
                    'homeroom_teacher' => $schoolClass->homeroomTeacher ? [
                        'name' => $schoolClass->homeroomTeacher->name,
                        'email' => $schoolClass->homeroomTeacher->email,
                    ] : null,
                    'teacher_count' => $this->mapTeacherMembers($schoolClass)->count(),
                    'student_count' => $this->mapStudents($schoolClass)->count(),
                    'subject_count' => $schoolClass->subjectTeachers->count(),
                ])->values(),
            'teachers' => $this->teacherOptions(),
            'status' => session('status'),
        ]);
    }

    public function show(SchoolClass $schoolClass): Response
    {
        $this->authorize('view', $schoolClass);

        $schoolClass->load([
            'homeroomTeacher:id,name,email',
            'members.user:id,name,email',
            'subjectTeachers.teacher:id,name,email',
        ]);

        return Inertia::render('Admin/Classes/Show', [
            'schoolClass' => [
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
                'teachers' => $this->mapTeacherMembers($schoolClass)->values(),
                'subject_teachers' => $schoolClass->subjectTeachers
                    ->sortBy('subject_name')
                    ->map(fn (ClassSubjectTeacher $subjectTeacher) => [
                        'id' => $subjectTeacher->id,
                        'subject_name' => $subjectTeacher->subject_name,
                        'teacher' => $subjectTeacher->teacher ? [
                            'id' => $subjectTeacher->teacher->id,
                            'name' => $subjectTeacher->teacher->name,
                            'email' => $subjectTeacher->teacher->email,
                        ] : null,
                    ])->values(),
                'students' => $this->mapStudents($schoolClass)->values(),
            ],
            'teachers' => $this->teacherOptions(),
            'students' => $this->studentOptions(),
            'status' => session('status'),
        ]);
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        $this->authorize('create', SchoolClass::class);

        $this->createSchoolClass->handle([
            'name' => $request->string('name')->toString(),
            'grade_level' => $request->string('grade_level')->toString(),
            'major' => $request->string('major')->toString() ?: null,
            'academic_year' => $request->string('academic_year')->toString(),
            'homeroom_teacher_id' => $request->integer('homeroom_teacher_id') ?: null,
        ], $request->user());

        return back()->with('status', 'Kelas berhasil dibuat.');
    }

    public function assignTeacher(AssignTeacherRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $this->authorize('assignTeacher', $schoolClass);

        $this->assignTeacherToClass->handle($schoolClass, $request->integer('user_id'));

        return back()->with('status', 'Guru berhasil ditambahkan ke kelas.');
    }

    public function enrollStudent(EnrollStudentRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $this->authorize('enrollStudent', $schoolClass);

        $this->enrollStudentToClass->handle($schoolClass, $request->integer('user_id'));

        return back()->with('status', 'Siswa berhasil dimasukkan ke kelas.');
    }

    public function removeTeacher(SchoolClass $schoolClass, User $user): RedirectResponse
    {
        $this->authorize('removeTeacher', $schoolClass);

        $this->removeTeacherFromClass->handle($schoolClass, $user->id);

        return back()->with('status', 'Guru berhasil dihapus dari kelas.');
    }

    public function removeStudent(SchoolClass $schoolClass, User $user): RedirectResponse
    {
        $this->authorize('removeStudent', $schoolClass);

        $this->removeStudentFromClass->handle($schoolClass, $user->id);

        return back()->with('status', 'Siswa berhasil dihapus dari kelas.');
    }

    public function assignSubjectTeacher(StoreSubjectTeacherRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $this->authorize('assignSubjectTeacher', $schoolClass);

        $this->assignSubjectTeacher->handle(
            $schoolClass,
            $request->integer('teacher_id'),
            $request->string('subject_name')->toString(),
        );

        return back()->with('status', 'Guru mata pelajaran berhasil ditetapkan.');
    }

    public function updateSubjectTeacher(
        UpdateSubjectTeacherRequest $request,
        SchoolClass $schoolClass,
        ClassSubjectTeacher $subjectTeacher,
    ): RedirectResponse {
        $this->authorize('updateSubjectTeacher', $schoolClass);
        $this->ensureSubjectTeacherBelongsToClass($schoolClass, $subjectTeacher);

        $this->updateSubjectTeacher->handle(
            $subjectTeacher,
            $request->integer('teacher_id'),
            $request->string('subject_name')->toString(),
        );

        return back()->with('status', 'Penugasan guru mata pelajaran berhasil diperbarui.');
    }

    public function removeSubjectTeacher(SchoolClass $schoolClass, ClassSubjectTeacher $subjectTeacher): RedirectResponse
    {
        $this->authorize('removeSubjectTeacher', $schoolClass);
        $this->ensureSubjectTeacherBelongsToClass($schoolClass, $subjectTeacher);

        $this->removeSubjectTeacher->handle($subjectTeacher);

        return back()->with('status', 'Penugasan guru mata pelajaran berhasil dihapus.');
    }

    private function teacherOptions()
    {
        return User::query()
            ->where('role', UserRole::Teacher)
            ->where('approval_status', ApprovalStatus::Approved)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function studentOptions()
    {
        return User::query()
            ->where('role', UserRole::Student)
            ->where('approval_status', ApprovalStatus::Approved)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function mapTeacherMembers(SchoolClass $schoolClass)
    {
        return $schoolClass->members
            ->whereIn('member_role', [ClassMemberRole::Teacher, ClassMemberRole::HomeroomTeacher])
            ->groupBy('user_id')
            ->map(function ($members) {
                /** @var ClassMember $firstMember */
                $firstMember = $members->first();

                return [
                    'id' => $firstMember->user_id,
                    'name' => $firstMember->user?->name,
                    'email' => $firstMember->user?->email,
                    'roles' => $members->map(fn (ClassMember $member) => $member->member_role->value)->values(),
                ];
            });
    }

    private function mapStudents(SchoolClass $schoolClass)
    {
        return $schoolClass->members
            ->where('member_role', ClassMemberRole::Student)
            ->map(fn (ClassMember $member) => [
                'id' => $member->user_id,
                'name' => $member->user?->name,
                'email' => $member->user?->email,
            ]);
    }

    private function ensureSubjectTeacherBelongsToClass(SchoolClass $schoolClass, ClassSubjectTeacher $subjectTeacher): void
    {
        abort_unless($subjectTeacher->school_class_id === $schoolClass->id, 404);
    }
}
