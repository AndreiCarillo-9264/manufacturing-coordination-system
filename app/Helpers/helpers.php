<?php

use App\Models\ActivityLog;

if (!function_exists('activity')) {
    /**
     * Log activity
     * 
     * Usage: 
     * activity()
     *     ->performedOn($model)
     *     ->causedBy($user)
     *     ->withProperties(['key' => 'value'])
     *     ->log('Action description');
     */
    function activity()
    {
        return new class {
            protected $model;
            protected $causer;
            protected $properties = [];

            public function performedOn($model)
            {
                $this->model = $model;
                return $this;
            }

            public function causedBy($user)
            {
                $this->causer = $user;
                return $this;
            }

            public function withProperties(array $properties)
            {
                $this->properties = $properties;
                return $this;
            }

            public function log(string $action)
            {
                ActivityLog::create([
                    'model_type' => $this->model ? get_class($this->model) : null,
                    'model_id' => $this->model ? $this->model->id : null,
                    'action' => $action,
                    'user_id' => $this->causer ? $this->causer->id : auth()->id(),
                    'old_values' => $this->properties['old'] ?? null,
                    'new_values' => $this->properties['new'] ?? $this->properties,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        };
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format number as currency
     */
    function formatCurrency($amount, $currency = 'PHP')
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('formatNumber')) {
    /**
     * Format number with commas
     */
    function formatNumber($number, $decimals = 0)
    {
        return number_format($number, $decimals);
    }
}

if (!function_exists('getStatusBadgeClass')) {
    /**
     * Get Tailwind CSS badge class based on status
     */
    function getStatusBadgeClass($status)
    {
        return match($status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-indigo-100 text-indigo-800',
            'completed', 'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'urgent' => 'bg-orange-100 text-orange-800',
            'backlog' => 'bg-purple-100 text-purple-800',
            'balance' => 'bg-yellow-100 text-yellow-800',
            'complete' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

if (!function_exists('getDepartmentBadgeClass')) {
    /**
     * Get Tailwind CSS badge class for department
     */
    function getDepartmentBadgeClass($department)
    {
        return match($department) {
            'admin' => 'bg-red-100 text-red-800',
            'sales' => 'bg-blue-100 text-blue-800',
            'production' => 'bg-green-100 text-green-800',
            'inventory' => 'bg-purple-100 text-purple-800',
            'logistics' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

if (!function_exists('calculatePercentage')) {
    /**
     * Calculate percentage
     */
    function calculatePercentage($part, $total, $decimals = 2)
    {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, $decimals);
    }
}