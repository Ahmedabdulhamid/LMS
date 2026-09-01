<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use App\Enums\SubscriptionStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'phone', 'image', 'bio', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'students' && $this->is_active;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        return Storage::disk(config('lms-upload.disk'))->url($this->image);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function courseReviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function coursePurchases(): HasMany
    {
        return $this->hasMany(CoursePurchase::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['source_type', 'source_id', 'starts_at', 'ends_at', 'status'])
            ->withTimestamps()
            ->wherePivot('status', EnrollmentStatus::Active->value)
            ->wherePivot('starts_at', '<=', now())
            ->where(fn ($query) => $query
                ->whereNull('enrollments.ends_at')
                ->orWhere('enrollments.ends_at', '>', now()))
            ->where(function ($query): void {
                $query
                    ->where('enrollments.source_type', '!=', EnrollmentSourceType::Subscription->value)
                    ->orWhereExists(function ($subscriptionQuery): void {
                        $subscriptionQuery
                            ->selectRaw('1')
                            ->from('subscriptions')
                            ->whereColumn('subscriptions.id', 'enrollments.source_id')
                            ->where('subscriptions.status', SubscriptionStatus::Active->value)
                            ->where('subscriptions.starts_at', '<=', now())
                            ->where(fn ($query) => $query
                                ->whereNull('subscriptions.ends_at')
                                ->orWhere('subscriptions.ends_at', '>', now()));
                    });
            })
            ->distinct();
    }

    public function purchasedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_purchases')
            ->withPivot(['price', 'coupon_code', 'coupon_discount', 'purchased_at', 'payment_status'])
            ->withTimestamps()
            ->wherePivot('payment_status', 'completed');
    }
}
