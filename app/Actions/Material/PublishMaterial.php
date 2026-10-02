<?php

namespace App\Actions\Material;

use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\SafeUploadedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PublishMaterial
{
    public function handle(SchoolClass $schoolClass, User $teacher, array $payload): Material
    {
        /** @var UploadedFile $file */
        $file = $payload['file'];

        return DB::transaction(function () use ($schoolClass, $teacher, $payload, $file) {
            $filePath = $file->store("materials/{$schoolClass->id}", 'local');

            return Material::create([
                'school_class_id' => $schoolClass->id,
                'teacher_id' => $teacher->id,
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'file_path' => $filePath,
                'file_name' => SafeUploadedFile::originalName($file),
                'file_type' => $file->getClientMimeType(),
                'published_at' => now(),
            ]);
        });
    }
}
