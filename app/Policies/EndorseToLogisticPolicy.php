<?php

namespace App\Policies;

use App\Models\EndorseToLogistic;
use App\Models\User;

class EndorseToLogisticPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->department, ['admin', 'logistics', 'sales']);
    }

    public function view(User $user, EndorseToLogistic $endorse): bool
    {
        return in_array($user->department, ['admin', 'logistics', 'sales']);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLogistics();
    }

    public function update(User $user, EndorseToLogistic $endorse): bool
    {
        return $user->isAdmin() || $user->isLogistics();
    }

    public function delete(User $user, EndorseToLogistic $endorse): bool
    {
        return $user->isAdmin() || $user->isLogistics();
    }

    public function restore(User $user, EndorseToLogistic $endorse): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, EndorseToLogistic $endorse): bool
    {
        return $user->isAdmin();
    }
}
