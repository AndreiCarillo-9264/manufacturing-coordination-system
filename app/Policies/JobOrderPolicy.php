<?php

namespace App\Policies;

use App\Models\JobOrder;
use App\Models\User;

class JobOrderPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin, Sales, Production, Logistics, Inventory can view job orders
        return in_array($user->department, ['admin', 'sales', 'production', 'logistics', 'inventory']);
    }

    public function view(User $user, JobOrder $jobOrder): bool
    {
        return in_array($user->department, ['admin', 'sales', 'production', 'logistics', 'inventory']);
    }

    public function create(User $user): bool
    {
        // Only Admin and Sales can create job orders
        return $user->isAdmin() || $user->isSales();
    }

    public function update(User $user, JobOrder $jobOrder): bool
    {
        // Admin and Sales can update
        return $user->isAdmin() || $user->isSales();
    }

    public function delete(User $user, JobOrder $jobOrder): bool
    {
        // Only Admin and Sales can delete
        return $user->isAdmin() || $user->isSales();
    }

    /**
     * Determine if user can approve job order
     */
    public function approve(User $user, JobOrder $jobOrder): bool
    {
        // Admin, Sales, and Inventory can approve
        // And only if status is pending
        return ($user->isAdmin() || $user->isSales() || $user->isInventory()) && $jobOrder->status === 'pending';
    }

    /**
     * Determine if user can cancel job order
     */
    public function cancel(User $user, JobOrder $jobOrder): bool
    {
        return ($user->isAdmin() || $user->isSales()) && 
               in_array($jobOrder->status, ['pending', 'approved']);
    }

    public function restore(User $user, JobOrder $jobOrder): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, JobOrder $jobOrder): bool
    {
        return $user->isAdmin();
    }
}