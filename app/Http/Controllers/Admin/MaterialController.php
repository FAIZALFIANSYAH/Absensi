<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Material\PublishMaterial;
use App\Http\Controllers\Controller;
use App\Http\Requests\Material\StoreMaterialRequest;
use App\Models\Material;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function __construct(
        private readonly PublishMaterial $publishMaterial,
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', Material::class);

        return Inertia::render('Admin/Materials/Index', [
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
            'materials' => Material::query()
                ->with(['schoolClass:id,name,grade_level,major,academic_year', 'teacher:id,name'])
                ->latest('published_at')
                ->get()
                ->map(fn (Material $material) => [
                    'id' => $material->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'file_name' => $material->file_name,
                    'file_type' => $material->file_type,
                    'published_at' => $material->published_at?->toIso8601String(),
                    'teacher_name' => $material->teacher?->name,
                    'school_class' => [
                        'id' => $material->schoolClass?->id,
                        'name' => $material->schoolClass?->name,
                        'grade_level' => $material->schoolClass?->grade_level,
                        'major' => $material->schoolClass?->major,
                        'academic_year' => $material->schoolClass?->academic_year,
                    ],
                    'download_url' => route('admin.materials.download', $material),
                ])->values(),
            'flashStatus' => session('status'),
        ]);
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($request->integer('school_class_id'));
        $this->authorize('create', [Material::class, $schoolClass]);

        $this->publishMaterial->handle($schoolClass, $request->user(), [
            'title' => $request->string('title')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'file' => $request->file('file'),
        ]);

        return back()->with('status', 'Materi berhasil dipublikasikan.');
    }

    public function download(Material $material): StreamedResponse
    {
        $this->authorize('view', $material);

        return Storage::disk('local')->download($material->file_path, $material->file_name);
    }
}
