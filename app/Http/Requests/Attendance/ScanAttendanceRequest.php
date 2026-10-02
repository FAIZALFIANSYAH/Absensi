<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use App\Services\QRCode\StudentQrIdentityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ScanAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_session_id' => 'required|exists:attendance_sessions,id',
            'qr_token' => 'required|string|max:255',
            'status' => ['required', new Enum(AttendanceStatus::class)],
            'notes' => 'nullable|string|max:2000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $qrToken = $this->input('qr_token');

        if (! is_string($qrToken)) {
            return;
        }

        $this->merge([
            'qr_token' => app(StudentQrIdentityService::class)->resolveToken($qrToken),
        ]);
    }
}
