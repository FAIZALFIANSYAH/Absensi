<?php

namespace App\Actions\Assignment;

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\SafeUploadedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PublishAssignment
{
    public function handle(SchoolClass $schoolClass, User $teacher, array $payload): Assignment
    {
        return DB::transaction(function () use ($schoolClass, $teacher, $payload) {
            /** @var UploadedFile|null $file */
            $file = $payload['file'] ?? null;
            $filePath = $file?->store("assignments/{$schoolClass->id}", 'local');

            return Assignment::create([
                'school_class_id' => $schoolClass->id,
                'teacher_id' => $teacher->id,
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'file_path' => $filePath,
                'file_name' => $file ? SafeUploadedFile::originalName($file) : null,
                'deadline_at' => $payload['deadline_at'],
                'status' => AssignmentStatus::Published,
            ]);
        });
    }
}
