<?php

namespace App\Http\Controllers\Student;

use App\Actions\Assignment\SubmitAssignment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assignment\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly SubmitAssignment $submitAssignment,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', Assignment::class);

        /** @var \App\Models\User $user */
        $user = request()->user()->load([
            'schoolClasses' => fn ($query) => $query->orderBy('name'),
        ]);

        $classIds = $user->schoolClasses->pluck('id')->all();

        $submissionByAssignmentId = Submission::query()
            ->where('student_id', $user->id)
            ->whereIn('assignment_id', Assignment::query()->whereIn('school_class_id', $classIds)->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return Inertia::render('Student/Assignments/Index', [
            'assignments' => Assignment::query()
                ->whereIn('school_class_id', $classIds)
                ->with('schoolClass:id,name,grade_level,major,academic_year')
                ->latest('deadline_at')
                ->get()
                ->map(function (Assignment $assignment) use ($submissionByAssignmentId) {
                    $submission = $submissionByAssignmentId->get($assignment->id);

                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'description' => $assignment->description,
                        'file_name' => $assignment->file_name,
                        'deadline_at' => $assignment->deadline_at?->toIso8601String(),
                        'status' => $assignment->status->value,
                        'school_class' => [
                            'name' => $assignment->schoolClass?->name,
                        ],
                        'download_url' => $assignment->file_path ? route('student.assignments.download', $assignment) : null,
                        'submission' => $submission ? [
                            'id' => $submission->id,
                            'file_name' => $submission->file_name,
                            'status' => $submission->status->value,
                            'submitted_at' => $submission->submitted_at?->toIso8601String(),
                            'notes' => $submission->notes,
                            'download_url' => route('student.submissions.download', $submission),
                        ] : null,
                    ];
                })->values(),
            'flashStatus' => session('status'),
        ]);
    }

    public function submit(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $assignment->load('schoolClass');
        $this->authorize('submit', $assignment);

        $this->submitAssignment->handle($assignment, $request->user(), [
            'file' => $request->file('file'),
            'notes' => $request->string('notes')->toString() ?: null,
        ]);

        return back()->with('status', 'Tugas berhasil dikumpulkan.');
    }

    public function download(Assignment $assignment): StreamedResponse
    {
        $this->authorize('view', $assignment);

        abort_unless($assignment->file_path, 404);

        return Storage::disk('local')->download($assignment->file_path, $assignment->file_name);
    }
}
