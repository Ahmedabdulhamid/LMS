<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Instructor;
use App\Models\OrderArchive;
use Illuminate\Contracts\Auth\Authenticatable;

class OrderArchivePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin || ($user instanceof Instructor && $user->is_active);
    }

    public function view(Authenticatable $user, OrderArchive $archive): bool
    {
        return $user instanceof Admin
            || ($user instanceof Instructor
                && $user->is_active
                && in_array($user->id, $archive->instructor_ids ?? [], strict: true));
    }

    public function create(Authenticatable $user): bool
    {
        return false;
    }

    public function update(Authenticatable $user, OrderArchive $archive): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, OrderArchive $archive): bool
    {
        return false;
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return false;
    }
}
