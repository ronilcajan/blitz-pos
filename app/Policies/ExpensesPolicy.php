<?php

namespace App\Policies;

use App\Models\Expenses;
use App\Models\User;

class ExpensesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('owner|admin|staff');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Expenses $expenses): bool
    {
        return $user->hasRole('owner|admin|staff') && 
        $user->store_id === $expenses->store_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('owner|admin|staff');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Expenses $expenses): bool
    {
        return $user->hasRole('owner|admin')
        && $user->store_id === $expenses->store_id;    
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expenses $expenses): bool
    {
        return $user->hasRole('owner|admin')
        && $user->store_id === $expenses->store_id;    
    }

    public function bulk_delete(User $user): bool
    {
        return $user->hasRole('owner|admin');
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Expenses $expenses): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Expenses $expenses): bool
    {
        //
    }
}
