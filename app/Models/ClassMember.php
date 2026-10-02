<?php

namespace App\Models;

use App\Enums\ClassMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_class_id',
        'user_id',
        'member_role',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'member_role' => ClassMemberRole::class,
            'joined_at' => 'datetime',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
