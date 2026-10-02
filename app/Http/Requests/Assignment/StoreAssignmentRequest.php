<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_class_id' => 'required|exists:school_classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'deadline_at' => 'required|date|after:now',
            'file' => [
                'nullable',
                File::types(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'])
                    ->max(10 * 1024),
            ],
        ];
    }
}
