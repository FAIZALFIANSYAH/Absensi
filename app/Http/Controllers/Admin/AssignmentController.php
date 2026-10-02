<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Assignment\PublishAssignment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assignment\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly PublishAssignment $publishAssignment,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', Assignment::class);

        return Inertia::render('Admin/Assignments/Index', [
            'classes' => SchoolClass::query()
                ->orderBy('name')
                ->get()
                ->map(fn (SchoolClass $schoolClass) => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'grade_level' => $schoolClass->grade_level,
                    'major' => $schoolClass->major,
                    'academic_year' => $schoolClass->academic_year,
                ])->values(),
            'assignments' => Assignment::query()
                ->with([
                    'schoolClass:id,name,grade_level,major,academic_year',
                    'submissions.student.studentProfile',
                ])
                ->latest('deadline_at')
                ->get()
                ->map(fn (Assignment $assignment) => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'file_name' => $assignment->file_name,
                    'deadline_at' => $assignment->deadline_at?->toIso8601String(),
                    'status' => $assignment->status->value,
                    'school_class' => [
                        'name' => $assignment->schoolClass?->name,
                    ],
                    'download_url' => $assignment->file_path ? route('admin.assignments.download', $assignment) : null,
                    'submission_count' => $assignment->submissions->count(),
                    'submissions' => $assignment->submissions->map(fn ($submission) => [
                        'id' => $submission->id,
                        'student_name' => $submission->student?->name,
                        'nis' => $submission->student?->studentProfile?->nis,
                        'status' => $submission->status->value,
                        'submitted_at' => $submission->submitted_at?->toIso8601String(),
                        'file_name' => $submission->file_name,
                    ])->values(),
                ])->values(),
            'flashStatus' => session('status'),
        ]);
    }

    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($request->integer('school_class_id'));
        $this->authorize('create', [Assignment::class, $schoolClass]);

        $this->publishAssignment->handle($schoolClass, $request->user(), [
            'title' => $request->string('title')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'deadline_at' => $request->date('deadline_at'),
            'file' => $request->file('file'),
        ]);

        return back()->with('status', 'Tugas berhasil dipublikasikan.');
    }

    public function download(Assignment $assignment): StreamedResponse
    {
        $this->authorize('view', $assignment);

        abort_unless($assignment->file_path, 404);

        return Storage::disk('local')->download($assignment->file_path, $assignment->file_name);
    }
}
