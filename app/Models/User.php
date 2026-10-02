<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'approval_status',
        'is_active',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'deactivated_by',
        'deactivated_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
            'approval_status' => ApprovalStatus::class,
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rejected_by');
    }

    public function deactivator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'deactivated_by');
    }

    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_members')
            ->withPivot(['member_role', 'joined_at'])
            ->withTimestamps();
    }

    public function openedAttendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'opened_by');
    }

    public function scannedAttendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'scanned_by');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function classSubjectTeachingAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'teacher_id');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === ApprovalStatus::Approved;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Superadmin;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function belongsToSchoolClass(SchoolClass|Model|int|null $schoolClass): bool
    {
        $schoolClassId = match (true) {
            $schoolClass instanceof SchoolClass => $schoolClass->id,
            $schoolClass instanceof Model => $schoolClass->getKey(),
            default => $schoolClass,
        };

        if (! $schoolClassId) {
            return false;
        }

        return $this->schoolClasses()
            ->where('school_classes.id', $schoolClassId)
            ->exists();
    }
}
