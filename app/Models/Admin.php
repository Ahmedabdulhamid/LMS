<?php

namespace App\Models;

use App\Services\LookUpService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'phone', 'image'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable implements FilamentUser
{
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

    /**
     * @return array<string, string>
     */
    protected static function booted()
    {
        static::deleted(function (Admin $admin) {
            if ($admin->image && Storage::disk(config('lms-upload.disk'))->exists($admin->image)) {
                return Storage::disk(config('lms-upload.disk'))->delete($admin->image);
            }
            app(LookUpService::class)->clearAdmins();
            app(LookUpService::class)->admins();
        });
        static::updating(function (Admin $admin) {
            if ($admin->isDirty('image')) {
                $originalImage = $admin->getOriginal('image');
                if ($originalImage && Storage::disk(config('lms-upload.disk'))->exists($originalImage)) {
                    Storage::disk(config('lms-upload.disk'))->delete($originalImage);
                }
            }
            app(LookUpService::class)->clearAdmins();
            app(LookUpService::class)->admins();
        });
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
