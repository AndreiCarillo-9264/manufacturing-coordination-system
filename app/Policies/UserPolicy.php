<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // Only admin can view users list
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        // Admin or viewing own profile
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        // Only admin can create users
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Admin or updating own profile (limited fields)
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Only admin can delete users, but not themselves
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}