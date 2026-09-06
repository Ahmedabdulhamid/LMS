<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class CoursePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return ! $user instanceof User || $user->is_active;
    }

    public function view(Authenticatable $user, Course $course): bool
    {
        return ! $user instanceof User || $user->enrolledCourses()->whereKey($course)->exists();
    }

    public function create(Authenticatable $user): bool
    {
        return ! $user instanceof User;
    }

    public function update(Authenticatable $user, Course $course): bool
    {
        return ! $user instanceof User;
    }

    public function delete(Authenticatable $user, Course $course): bool
    {
        return ! $user instanceof User;
    }
}
