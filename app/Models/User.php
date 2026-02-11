<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $fillable = [
        'username',
        'name',
        'department',
        'email',
        'password',
        'profile_picture',
        'is_active',
        'deactivation_remarks',
        'deactivated_by',
        'deactivated_at',
        'last_login_at',
    ];

    // Default attribute values
    protected $attributes = [
        'is_active' => true,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'name', 'department', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$this->username} {$eventName}");
    }

    // ========== SCOPES ==========
    
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('username', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%");
        });
    }

    // ========== DEPARTMENT CHECK METHODS ==========
    
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

    public function hasRole(string $role): bool
    {
        // Map role names to departments for compatibility
        $roleMap = [
            'admin' => 'admin',
            'sales' => 'sales',
            'production' => 'production',
            'inventory' => 'inventory',
            'logistics' => 'logistics',
        ];

        $department = $roleMap[strtolower($role)] ?? strtolower($role);
        return $this->department === $department;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $permission): bool
    {
        // Define department-based permissions
        $permissions = [
            'admin' => ['*'], // All permissions
            'sales' => ['products.view', 'jobOrders.create', 'jobOrders.view', 'deliverySchedules.view'],
            'production' => ['jobOrders.view', 'inventoryTransfers.create', 'inventoryTransfers.view'],
            'inventory' => ['actualInventory.create', 'actualInventory.view', 'finishedGoods.view'],
            'logistics' => ['endorseToLogistics.create', 'endorseToLogistics.view', 'deliverySchedules.view'],
        ];

        $departmentPermissions = $permissions[$this->department] ?? [];

        // Admin has all permissions
        if (in_array('*', $departmentPermissions)) {
            return true;
        }

        return in_array($permission, $departmentPermissions);
    }

    // ========== RELATIONSHIPS ==========
    
    // Products
    public function encodedProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'encoded_by');
    }

    public function updatedProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'updated_by');
    }

    // Job Orders
    public function encodedJobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'encoded_by');
    }

    public function approvedJobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'approved_by');
    }

    public function updatedJobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'updated_by');
    }

    // Delivery Schedules
    public function encodedDeliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class, 'encoded_by');
    }

    public function updatedDeliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class, 'updated_by');
    }

    // Inventory Transfers
    public function encodedInventoryTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'encoded_by');
    }

    public function updatedInventoryTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'updated_by');
    }

    // Legacy relationship name
    public function receivedTransfers(): HasMany
    {
        // Note: received_by is now a string (name) not a user_id
        // This relationship won't work as before
        return $this->hasMany(InventoryTransfer::class, 'received_by');
    }

    // Endorse to Logistics
    public function encodedEndorseToLogistics(): HasMany
    {
        return $this->hasMany(EndorseToLogistic::class, 'encoded_by');
    }

    public function updatedEndorseToLogistics(): HasMany
    {
        return $this->hasMany(EndorseToLogistic::class, 'updated_by');
    }

    // Finished Goods
    public function encodedFinishedGoods(): HasMany
    {
        return $this->hasMany(FinishedGood::class, 'encoded_by');
    }

    public function updatedFinishedGoods(): HasMany
    {
        return $this->hasMany(FinishedGood::class, 'updated_by');
    }

    // Actual Inventory
    public function encodedActualInventory(): HasMany
    {
        return $this->hasMany(ActualInventory::class, 'encoded_by');
    }

    public function updatedActualInventory(): HasMany
    {
        return $this->hasMany(ActualInventory::class, 'updated_by');
    }

    // Note: counted_by and verified_by are now strings, not user IDs
    // These legacy relationships won't work
    public function countedInventories(): HasMany
    {
        return $this->hasMany(ActualInventory::class, 'counted_by');
    }

    public function verifiedInventories(): HasMany
    {
        return $this->hasMany(ActualInventory::class, 'verified_by');
    }

    // Conversations & Chat
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function chatHistories(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }

    // Activity Logs
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ========== HELPER METHODS ==========
    
    public function activate(): self
    {
        $this->is_active = true;
        $this->save();
        return $this;
    }

    public function deactivate(): self
    {
        $this->is_active = false;
        $this->save();
        return $this;
    }

    public function updateProfile(array $data): self
    {
        $this->fill($data);
        $this->save();
        return $this;
    }

    public function changeDepartment(string $department): self
    {
        if (!in_array($department, ['admin', 'sales', 'production', 'inventory', 'logistics'])) {
            throw new \InvalidArgumentException("Invalid department: {$department}");
        }

        $this->department = $department;
        $this->save();
        return $this;
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getDepartmentLabelAttribute(): string
    {
        return ucfirst($this->department);
    }

    public function getIsActiveUserAttribute(): bool
    {
        return $this->is_active ?? true;
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->username})";
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        // Return default avatar or gravatar
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=random";
    }

    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    // ========== STATISTICS ==========
    
    public function getTotalEncodedRecordsAttribute(): int
    {
        return $this->encodedProducts()->count() +
               $this->encodedJobOrders()->count() +
               $this->encodedDeliverySchedules()->count() +
               $this->encodedInventoryTransfers()->count() +
               $this->encodedFinishedGoods()->count();
    }

    public function getRecentActivityCountAttribute(): int
    {
        return $this->activityLogs()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    public function getApprovedJobOrdersCountAttribute(): int
    {
        return $this->approvedJobOrders()->count();
    }
}