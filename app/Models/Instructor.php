<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable([
    'name',
    'slug',
    'email',
    'password',
    'bio',
    'small_description',
    'profile_picture',
    'phone',
    'gender',
    'birthday',
    'educations',
    'certifications',
    'skills',
    'experiences',
    'specialization',
    'achivements',
    'years_of_experience',
    'linkedin_url',
    'facebook_url',
    'twitter_url',
    'youtube_url',
    'is_active',
    'email_verified_at',
    'rank_id',
])]
#[Hidden(['password'])]
class Instructor extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasSlug, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'instructors' && $this->is_active;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->profile_picture)) {
            return null;
        }

        return Storage::disk(config('lms-upload.disk'))->url($this->profile_picture);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'birthday' => 'date',
            'educations' => 'array',
            'certifications' => 'array',
            'skills' => 'array',
            'experiences' => 'array',
            'specialization' => 'array',
            'achivements' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
