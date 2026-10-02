<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Services\ClassPortalService;
use Inertia\Inertia;
use Inertia\Response;

class ClassController extends Controller
{
    public function __construct(
        private readonly ClassPortalService $classPortalService,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', SchoolClass::class);

        /** @var \App\Models\User $user */
        $user = request()->user();

        return Inertia::render('Student/Classes/Index', [
            'classes' => $this->classPortalService->studentIndexFor($user),
        ]);
    }

    public function show(SchoolClass $schoolClass): Response
    {
        $this->authorize('view', $schoolClass);

        /** @var \App\Models\User $user */
        $user = request()->user();

        return Inertia::render('Student/Classes/Show', [
            'portal' => $this->classPortalService->studentPortal($schoolClass, $user),
        ]);
    }
}
