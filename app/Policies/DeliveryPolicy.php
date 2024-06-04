<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
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
    public function view(User $user, Delivery $delivery): bool
    {
        return $user->hasRole('owner|admin|staff') &&
        $user->store_id === $delivery->store_id;
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
    public function update(User $user, Delivery $delivery): bool
    {
        return $user->hasRole('owner|admin')
        && $user->store_id === $delivery->store_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Delivery $delivery): bool
    {
        return $user->hasRole('owner|admin')
        && $user->store_id === $delivery->store_id;
    }

    public function bulk_delete(User $user): bool
    {
        return $user->hasRole('owner|admin');
    }
}
