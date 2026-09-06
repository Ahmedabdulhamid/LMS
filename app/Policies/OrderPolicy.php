<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;

class OrderPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin || ($user instanceof Instructor && $user->is_active);
    }

    public function view(Authenticatable $user, Order $order): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return $user instanceof Instructor
            && $user->is_active
            && $order->items()
                ->whereHasMorph('purchasable', [Course::class], fn ($query) => $query->where('instructor_id', $user->id))
                ->exists();
    }

    public function create(Authenticatable $user): bool
    {
        return false;
    }

    public function update(Authenticatable $user, Order $order): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, Order $order): bool
    {
        return false;
    }
}
