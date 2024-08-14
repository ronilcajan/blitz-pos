<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users-read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users-create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users-update')
        && $user->store_id === $model->store_id;
    }

    public function reset(User $user, User $model): bool
    {
        return $user->hasPermission('users-update')
        && $user->store_id === $model->store_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users-delete')
               && $user->store_id === $model->store_id;
    }

    public function bulk_delete(User $user): bool
    {
        return $user->hasPermission('users-delete');
    }

    public function impersonate(User $user): bool
    {
        return $user->hasRole('superadministrator');
    }
}
