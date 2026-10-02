<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Material::class);

        $user = request()->user()->load([
            'schoolClasses' => fn ($query) => $query->orderBy('name'),
        ]);

        $classIds = $user->schoolClasses->pluck('id')->all();

        return Inertia::render('Student/Materials/Index', [
            'materials' => Material::query()
                ->whereIn('school_class_id', $classIds)
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
                    'download_url' => route('student.materials.download', $material),
                ])->values(),
        ]);
    }

    public function download(Material $material): StreamedResponse
    {
        $this->authorize('view', $material);

        return Storage::disk('local')->download($material->file_path, $material->file_name);
    }
}
