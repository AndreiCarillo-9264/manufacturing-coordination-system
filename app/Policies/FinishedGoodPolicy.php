<?php

namespace App\Policies;

use App\Models\FinishedGood;
use App\Models\User;

class FinishedGoodPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin, Inventory, Production, Logistics can view
        return in_array($user->department, ['admin', 'inventory', 'production', 'logistics']);
    }

    public function view(User $user, FinishedGood $finishedGood): bool
    {
        return in_array($user->department, ['admin', 'inventory', 'production', 'logistics']);
    }

    public function create(User $user): bool
    {
        // Only Admin and Inventory can create (though usually auto-created)
        return $user->isAdmin() || $user->isInventory();
    }

    public function update(User $user, FinishedGood $finishedGood): bool
    {
        // Admin and Inventory can update
        return $user->isAdmin() || $user->isInventory();
    }

    public function delete(User $user, FinishedGood $finishedGood): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, FinishedGood $finishedGood): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, FinishedGood $finishedGood): bool
    {
        return $user->isAdmin();
    }
}