<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin, Production, Inventory can view transfers
        return in_array($user->department, ['admin', 'production', 'inventory']);
    }

    public function view(User $user, Transfer $transfer): bool
    {
        return in_array($user->department, ['admin', 'production', 'inventory']);
    }

    public function create(User $user): bool
    {
        // Admin and Production can create transfers
        return $user->isAdmin() || $user->isProduction();
    }

    public function update(User $user, Transfer $transfer): bool
    {
        return $user->isAdmin() || $user->isProduction();
    }

    public function delete(User $user, Transfer $transfer): bool
    {
        return $user->isAdmin() || $user->isProduction();
    }

    public function restore(User $user, Transfer $transfer): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Transfer $transfer): bool
    {
        return $user->isAdmin();
    }
}