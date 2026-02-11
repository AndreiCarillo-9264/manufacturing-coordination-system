<?php

namespace App\Policies;

use App\Models\InventoryTransfer;
use App\Models\User;

class InventoryTransferPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin, Production, Inventory can view transfers
        return in_array($user->department, ['admin', 'production', 'inventory']);
    }

    public function view(User $user, InventoryTransfer $transfer): bool
    {
        return in_array($user->department, ['admin', 'production', 'inventory']);
    }

    public function create(User $user): bool
    {
        // Admin and Production can create transfers
        return $user->isAdmin() || $user->isProduction();
    }

    public function update(User $user, InventoryTransfer $transfer): bool
    {
        return $user->isAdmin() || $user->isProduction();
    }

    public function delete(User $user, InventoryTransfer $transfer): bool
    {
        return $user->isAdmin() || $user->isProduction();
    }

    public function restore(User $user, InventoryTransfer $transfer): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, InventoryTransfer $transfer): bool
    {
        return $user->isAdmin();
    }
}