<?php

namespace App\Services\QRCode;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Arr;

class StudentQrIdentityService
{
    public function ensureForUser(User $user): ?StudentProfile
    {
        $profile = $user->relationLoaded('studentProfile')
            ? $user->studentProfile
            : $user->studentProfile()->first();

        if (! $profile) {
            return null;
        }

        $profile->ensureQrToken();

        return $profile->fresh();
    }

    public function payload(?StudentProfile $profile): ?array
    {
        if (! $profile) {
            return null;
        }

        return [
            'v' => 1,
            'token' => $profile->qr_token,
            'issued_at' => $profile->qr_generated_at?->toIso8601String(),
        ];
    }

    public function resolveToken(string $payload): string
    {
        $payload = trim($payload);

        if ($payload === '') {
            return $payload;
        }

        if (! str_starts_with($payload, '{')) {
            return $payload;
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return $payload;
        }

        return (string) Arr::get($decoded, 'token', $payload);
    }
}
