<?php

namespace App\Models;

use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'source_type' => EnrollmentSourceType::class,
        'status' => EnrollmentStatus::class,
        'source_id' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', EnrollmentStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()));
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->id : $user);
    }

    public function scopeForCourse(Builder $query, Course|int $course): Builder
    {
        return $query->where('course_id', $course instanceof Course ? $course->id : $course);
    }
}
