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
        // Admin, Sales and Production can update (production can change status)
        return $user->isAdmin() || $user->isSales() || $user->isProduction();
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
        // And only if status is Pending (capitalized)
        return ($user->isAdmin() || $user->isSales() || $user->isInventory()) && $jobOrder->jo_status === 'Pending';
    }

    /**
     * Determine if user can cancel job order
     */
    public function cancel(User $user, JobOrder $jobOrder): bool
    {
        // Only allow cancellation of Pending status (not Partial, JO Full, or Cancelled)
        return ($user->isAdmin() || $user->isSales()) && $jobOrder->jo_status === 'Pending';
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