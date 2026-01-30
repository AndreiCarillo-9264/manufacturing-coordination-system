<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'department', 'profile_picture'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function encodedJobOrders()
    {
        return $this->hasMany(JobOrder::class, 'encoded_by_user_id');
    }

    public function receivedTransfers()
    {
        return $this->hasMany(Transfer::class, 'received_by_user_id');
    }

    public function countedInventories()
    {
        return $this->hasMany(ActualInventory::class, 'counted_by_user_id');
    }

    public function verifiedInventories()
    {
        return $this->hasMany(ActualInventory::class, 'verified_by_user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->department === 'admin';
    }

    public function isSales(): bool
    {
        return $this->department === 'sales';
    }

    public function isProduction(): bool
    {
        return $this->department === 'production';
    }

    public function isInventory(): bool
    {
        return $this->department === 'inventory';
    }

    public function isLogistics(): bool
    {
        return $this->department === 'logistics';
    }
}
