<?php

namespace App\Policies;

use App\Models\ActualInventory;
use App\Models\User;

class ActualInventoryPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin and Inventory can view
        return $user->isAdmin() || $user->isInventory();
    }

    public function view(User $user, ActualInventory $actualInventory): bool
    {
        return $user->isAdmin() || $user->isInventory();
    }

    public function create(User $user): bool
    {
        // Admin and Inventory can create
        return $user->isAdmin() || $user->isInventory();
    }

    public function update(User $user, ActualInventory $actualInventory): bool
    {
        return $user->isAdmin() || $user->isInventory();
    }

    public function delete(User $user, ActualInventory $actualInventory): bool
    {
        return $user->isAdmin() || $user->isInventory();
    }

    public function restore(User $user, ActualInventory $actualInventory): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, ActualInventory $actualInventory): bool
    {
        return $user->isAdmin();
    }
}