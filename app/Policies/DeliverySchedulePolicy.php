<?php

namespace App\Policies;

use App\Models\DeliverySchedule;
use App\Models\User;

class DeliverySchedulePolicy
{
    public function viewAny(User $user): bool
    {
        // Admin, Logistics, Sales can view delivery schedules
        return in_array($user->department, ['admin', 'logistics', 'sales']);
    }

    public function view(User $user, DeliverySchedule $deliverySchedule): bool
    {
        return in_array($user->department, ['admin', 'logistics', 'sales']);
    }

    public function create(User $user): bool
    {
        // Admin and Logistics can create delivery schedules
        return $user->isAdmin() || $user->isLogistics();
    }

    public function update(User $user, DeliverySchedule $deliverySchedule): bool
    {
        return $user->isAdmin() || $user->isLogistics();
    }

    public function delete(User $user, DeliverySchedule $deliverySchedule): bool
    {
        return $user->isAdmin() || $user->isLogistics();
    }

    /**
     * Determine if user can mark delivery as delivered
     */
    public function markDelivered(User $user, DeliverySchedule $deliverySchedule): bool
    {
        return ($user->isAdmin() || $user->isLogistics()) && 
               $deliverySchedule->ds_status !== 'delivered';
    }

    public function restore(User $user, DeliverySchedule $deliverySchedule): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, DeliverySchedule $deliverySchedule): bool
    {
        return $user->isAdmin();
    }
}