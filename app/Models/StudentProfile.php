<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nis',
        'nik',
        'phone',
        'gender',
        'birth_date',
        'address',
        'qr_token',
        'qr_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'qr_generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ensureQrToken(): void
    {
        if ($this->qr_token) {
            return;
        }

        $this->forceFill([
            'qr_token' => (string) Str::uuid(),
            'qr_generated_at' => now(),
        ])->save();
    }

    public function qrPayload(): array
    {
        return [
            'v' => 1,
            'token' => $this->qr_token,
            'issued_at' => $this->qr_generated_at?->toIso8601String(),
        ];
    }
}
