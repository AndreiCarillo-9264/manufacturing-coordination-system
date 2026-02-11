<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Traits\AutoFillsFromProduct;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes, AutoFillsFromProduct, TracksUser, LogsActivity;

    protected $fillable = [
        'jo_number',
        'po_number',
        'jo_status',
        'product_id',
        
        // Auto-filled from product
        'product_code',
        'customer_name',
        'model_name',
        'description',
        'dimension',
        'uom',
        
        // Job Order specific
        'quantity',
        'jo_balance',
        'ppqc_transfer',
        'ds_quantity',
        'withdrawal_status',
        'withdrawal_number',
        'week_number',
        'date_needed',
        'date_encoded',
        'date_approved',
        'remarks',
        
        // Audit fields
        'encoded_by',
        'approved_by',
        'updated_by',
    ];

    protected $casts = [
        'date_needed' => 'date',
        'date_encoded' => 'datetime',
        'date_approved' => 'datetime',
        'deactivated_at' => 'datetime',
        'quantity' => 'integer',
        'jo_balance' => 'integer',
        'ppqc_transfer' => 'integer',
        'ds_quantity' => 'integer',
    ];

    // Backward compatibility - expose old field names
    protected $appends = ['qty', 'status'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Job Order {$this->jo_number} {$eventName}");
    }

    // ========== RELATIONSHIPS ==========
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function encodedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    public function inventoryTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class);
    }

    // Legacy relationship name
    public function transfers(): HasMany
    {
        return $this->inventoryTransfers();
    }

    // ========== SCOPES ==========
    
    public function scopePending($query)
    {
        return $query->where('jo_status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('jo_status', 'Approved');
    }

    public function scopeJoFull($query)
    {
        return $query->where('jo_status', 'JO Full');
    }

    public function scopeCancelled($query)
    {
        return $query->where('jo_status', 'Cancelled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('jo_status', 'In Progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('jo_status', 'JO Full');
    }

    // ========== HELPER METHODS ==========
    
    public function approve(int $approvedBy = null): void
    {
        // Mark the approval
        $this->date_approved = now();
        $this->approved_by = $approvedBy ?? auth()->id();
        
        // Change status from Pending to Approved
        if ($this->jo_status === 'Pending') {
            $this->jo_status = 'Approved';
        }
        
        $this->save();
    }

    public function markApproved(): void
    {
        $this->update(['jo_status' => 'Approved']);
    }

    public function markFull(): void
    {
        $this->update(['jo_status' => 'JO Full']);
    }

    public function cancel(): void
    {
        $this->update(['jo_status' => 'Cancelled']);
    }

    public function calculateBalance(): void
    {
        $this->jo_balance = $this->quantity - (
            ($this->ppqc_transfer ?? 0) +
            ($this->ds_quantity ?? 0)
        );
        $this->save();
    }

    public function updateStatus(): void
    {
        if ($this->jo_balance <= 0) {
            $this->jo_status = 'JO Full';
        } elseif ($this->ppqc_transfer > 0 || $this->ds_quantity > 0) {
            $this->jo_status = 'Approved';
        } else {
            $this->jo_status = 'Pending';
        }
        $this->save();
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    // Old: qty_ordered -> New: quantity
    public function getQtyAttribute()
    {
        return $this->quantity;
    }

    public function setQtyAttribute($value): void
    {
        $this->attributes['quantity'] = $value;
    }

    // Old: status -> New: jo_status
    public function getStatusAttribute()
    {
        return $this->jo_status;
    }

    public function setStatusAttribute($value): void
    {
        // Map old status values to new enum values
        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'in_progress' => 'In Progress',
            'completed' => 'JO Full',
            'cancelled' => 'Cancelled',
        ];
        
        $this->attributes['jo_status'] = $statusMap[strtolower($value)] ?? $value;
    }

    // Ensure backward compatibility for encodedBy relationship
    public function encodedBy(): BelongsTo
    {
        return $this->encodedByUser();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->approvedByUser();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->updatedByUser();
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (JobOrder $jobOrder) {
            // Auto-generate jo_number: JO-YYYY-NNNN
            if (empty($jobOrder->jo_number)) {
                $jobOrder->jo_number = static::nextJoNumber();
            }

            // Auto-generate po_number if not provided
            if (empty($jobOrder->po_number)) {
                $jobOrder->po_number = static::nextPoNumber();
            }

            // Set initial jo_balance
            if (!isset($jobOrder->jo_balance)) {
                $jobOrder->jo_balance = $jobOrder->quantity;
            }

            // Calculate week_number from date_needed
            if ($jobOrder->date_needed && empty($jobOrder->week_number)) {
                $jobOrder->week_number = (string) Carbon::parse($jobOrder->date_needed)->format('W');
            }
        });

        static::updating(function (JobOrder $jobOrder) {
            // Recalculate week_number if date_needed changes
            if ($jobOrder->isDirty('date_needed') && $jobOrder->date_needed) {
                $jobOrder->week_number = (string) Carbon::parse($jobOrder->date_needed)->format('W');
            }
        });
    }

    // ========== CODE GENERATION HELPERS ==========
    
    public static function nextJoNumber(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('jo_number', 'like', "JO-{$year}-%")
            ->orderBy('jo_number', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->jo_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        
        return sprintf('JO-%d-%04d', $year, $nextNumber);
    }

    public static function nextPoNumber($date = null): string
    {
        $dt = $date ? Carbon::parse($date) : Carbon::now();
        $year = $dt->year;
        $month = $dt->month;
        $prefix = sprintf('PO-%d-%02d-', $year, $month);

        $last = static::where('po_number', 'like', "{$prefix}%")
            ->orderBy('po_number', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->po_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('PO-%d-%02d-%04d', $year, $month, $nextNumber);
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getFulfillmentPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        $fulfilled = $this->ppqc_transfer + $this->ds_quantity;
        return ($fulfilled / $this->quantity) * 100;
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->jo_balance);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->date_needed) {
            return false;
        }

        return $this->date_needed->isPast() && $this->jo_status !== 'JO Full';
    }

}