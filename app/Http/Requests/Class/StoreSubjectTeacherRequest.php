<?php

namespace App\Http\Requests\Class;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var SchoolClass|null $schoolClass */
        $schoolClass = $this->route('schoolClass');

        return [
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', UserRole::Teacher->value)
                    ->where('approval_status', ApprovalStatus::Approved->value)),
            ],
            'subject_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_subject_teachers', 'subject_name')
                    ->where(fn ($query) => $query->where('school_class_id', $schoolClass?->id)),
            ],
        ];
    }
}
