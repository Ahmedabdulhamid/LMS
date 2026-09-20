<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class CoursePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin
            || ($user instanceof Instructor && $user->is_active)
            || ($user instanceof User && $user->is_active);
    }

    public function view(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin
            || ($user instanceof Instructor && $user->is_active && (int) $course->instructor_id === (int) $user->id)
            || ($user instanceof User && $user->is_active && $user->enrolledCourses()->whereKey($course)->exists());
    }

    public function create(Authenticatable $user): bool
    {
        return $user instanceof Admin || ($user instanceof Instructor && $user->is_active);
    }

    public function update(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin
            || ($user instanceof Instructor && $user->is_active && (int) $course->instructor_id === (int) $user->id);
    }

    public function delete(Authenticatable $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return $user instanceof Admin || ($user instanceof Instructor && $user->is_active);
    }

    public function restore(Authenticatable $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function forceDelete(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin;
    }
}
