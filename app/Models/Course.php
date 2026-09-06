<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[Fillable([
    'instructor_id',
    'category_id',
    'title',
    'description',
    'price',
    'price_after_discount',
    'lang',
    'thumbnail',
    'level',
    'is_published',
])]
class Course extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'price_after_discount' => 'decimal:2',
        'duration' => 'float',
        'students_count' => 'integer',
        'number_lessons' => 'integer',
        'is_published' => 'boolean',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['source_type', 'source_id', 'starts_at', 'ends_at', 'status'])
            ->withTimestamps()
            ->wherePivot('status', EnrollmentStatus::Active->value)
            ->wherePivot('starts_at', '<=', now())
            ->where(fn ($query) => $query
                ->whereNull('enrollments.ends_at')
                ->orWhere('enrollments.ends_at', '>', now()))
            ->distinct();
    }

    public function goals(): HasMany
    {
        return $this->hasMany(IntendedLearner::class)->orderBy('order');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(CourseRequirement::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CoursePurchase::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function isFree(): bool
    {
        return (float) $this->effectivePrice() <= 0;
    }

    public function effectivePrice(): string
    {
        $price = (float) $this->price;
        $discounted = $this->price_after_discount !== null ? (float) $this->price_after_discount : $price;

        return number_format(max(0, min($price, $discounted)), 2, '.', '');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'purchasable');
    }

    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'plan_course')->withTimestamps();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function videos(): HasManyThrough
    {
        return $this->hasManyThrough(CourseVideo::class, Section::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            $course->slug = static::generateUniqueSlug($course->title);
        });

        static::updating(function (Course $course) {
            if ($course->isDirty('title')) {
                $course->slug = static::generateUniqueSlug($course->title, $course->id);
            }
            Cache::forget("course_{$course->id}");
        });

        static::deleting(function (Course $course) {
            Cache::forget("course_{$course->id}");
        });
    }

    private static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $index = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $original.'-'.$index++;
        }

        return $slug;
    }

    private static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = static::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function courseReviews()
    {
        return $this->hasMany(CourseReview::class);
    }
}
