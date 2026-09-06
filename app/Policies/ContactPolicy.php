<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $Admin): bool
    {
        return true;
    }

    /**
     * Determine whether the Admin can view the model.
     */
    public function view(Admin $Admin, Contact $contact): bool
    {
        return true;
    }

    /**
     * Determine whether the Admin can create models.
     */
    public function create(Admin $Admin): bool
    {
        return false;
    }

    /**
     * Determine whether the Admin can update the model.
     */
    public function update(Admin $Admin, Contact $contact): bool
    {
        return false;
    }

    /**
     * Determine whether the Admin can delete the model.
     */
    public function delete(Admin $Admin, Contact $contact): bool
    {
        return true;
    }

    /**
     * Determine whether the Admin can restore the model.
     */
    public function restore(Admin $Admin, Contact $contact): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Contact $contact): bool
    {
        return false;
    }
}
