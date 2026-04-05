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

class DeliverySchedule extends Model
{
    use HasFactory, SoftDeletes, AutoFillsFromProduct, TracksUser, LogsActivity;

    protected $fillable = [
        'ds_code',
        'ds_status',
        'job_order_id',
        'product_id',
        
        // Auto-filled from product
        'product_code',
        'customer_name',
        'model_name',
        'description',
        'dimension',
        'uom',
        
        // Auto-filled from job order
        'jo_number',
        'po_number',
        
        // Delivery Schedule specific
        'delivery_date',
        'delivery_time',
        'week_number',
        'quantity',
        'max_quantity',
        'fg_stocks',
        'jo_balance',
        'transfer_quantity',
        'delivered_quantity',
        'status',
        'pmp_commitment',
        'ppqc_commitment',
        'ppqc_status',
        'jo_status',
        'dsd',
        'buffer_stocks',
        'remarks',
        'delivery_remarks',
        'jo_remarks',
        
        // Audit fields
        'encoded_by',
        'date_encoded',
        'updated_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'date_encoded' => 'datetime',
        'quantity' => 'integer',
        'max_quantity' => 'integer',
        'fg_stocks' => 'integer',
        'jo_balance' => 'integer',
        'transfer_quantity' => 'integer',
        'delivered_quantity' => 'integer',
        'buffer_stocks' => 'integer',
    ];

    // Backward compatibility
    protected $appends = ['qty', 'date', 'jo_id', 'is_delayed', 'is_overdue', 'has_sufficient_stock', 'schedule_code'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Delivery Schedule {$this->ds_code} {$eventName}");
    }

    // ========== RELATIONSHIPS ==========
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function encodedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function endorseToLogistics(): HasMany
    {
        return $this->hasMany(EndorseToLogistic::class);
    }

    // ========== SCOPES ==========
    
    public function scopePending($query)
    {
        return $query->where('ds_status', 'ON SCHEDULE');
    }

    public function scopeUrgent($query)
    {
        return $query->where('status', 'Urgent');
    }

    public function scopeBacklog($query)
    {
        return $query->where('ds_status', 'BACKLOG');
    }

    public function scopeComplete($query)
    {
        return $query->where('ds_status', 'DELIVERED');
    }

    public function scopeDelivered($query)
    {
        return $query->where('ds_status', 'DELIVERED');
    }

    public function scopeDelayed($query)
    {
        return $query->where('delivery_date', '<', now())
                     ->where('ds_status', '!=', 'DELIVERED');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('delivery_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        $weekNumber = date('W');
        return $query->where('week_number', $weekNumber);
    }

    public function scopeOverdue($query)
    {
        return $query->where('delivery_date', '<', now())
            ->where('ds_status', '!=', 'DELIVERED');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('ds_status', $status);
    }

    // ========== HELPER METHODS ==========
    
    public function markUrgent(): void
    {
        $this->update(['status' => 'Urgent']);
    }

    public function markComplete(): void
    {
        $this->update(['ds_status' => 'DELIVERED']);
    }

    public function markAsDelivered(int $quantity = null): self
    {
        $this->delivered_quantity = $quantity ?? $this->quantity;
        $this->ds_status = 'DELIVERED';
        $this->save();

        return $this;
    }

    public function isDelayed(): bool
    {
        return $this->delivery_date && 
               $this->delivery_date->isPast() && 
               $this->ds_status !== 'DELIVERED';
    }

    public function isOverdue(): bool
    {
        return $this->delivery_date < now() && $this->ds_status !== 'DELIVERED';
    }

    public function calculateBacklog(): void
    {
        $backlog = $this->quantity - (
            ($this->fg_stocks ?? 0) +
            ($this->transfer_quantity ?? 0)
        );
        
        // Update status based on backlog
        if ($backlog > 0 && $this->delivery_date < now()) {
            $this->ds_status = 'BACKLOG';
            $this->save();
        }
    }

    public function updateDeliveryStatus(): self
    {
        // Check if fully delivered
        if ($this->delivered_quantity >= $this->quantity) {
            $this->ds_status = 'DELIVERED';
        }
        // Check if on schedule
        elseif ($this->delivery_date && $this->delivery_date >= now()) {
            $this->ds_status = 'ON SCHEDULE';
        }
        // Check if backlog
        elseif ($this->delivery_date && $this->delivery_date < now() && $this->delivered_quantity < $this->quantity) {
            $this->ds_status = 'BACKLOG';
        }

        return $this;
    }

    public function hasSufficientStock(): bool
    {
        return $this->fg_stocks >= $this->remaining_quantity;
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    // Old: qty_scheduled -> New: quantity
    public function getQtyAttribute()
    {
        return $this->quantity;
    }

    public function setQtyAttribute($value): void
    {
        $this->attributes['quantity'] = $value;
    }

    // Old: delivery_date is same as new
    public function getDateAttribute()
    {
        return $this->delivery_date;
    }

    // Old: job_order_id accessor for jo_id
    public function getJoIdAttribute()
    {
        return $this->job_order_id;
    }

    public function setJoIdAttribute($value): void
    {
        $this->attributes['job_order_id'] = $value;
    }

    public function getUomAttribute()
    {
        return $this->attributes['uom'] ?? $this->product?->uom;
    }

    // Backward compatibility: schedule_code -> ds_code
    public function getScheduleCodeAttribute()
    {
        return $this->ds_code;
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (DeliverySchedule $deliverySchedule) {
            // Auto-generate ds_code
            if (empty($deliverySchedule->ds_code)) {
                $deliverySchedule->ds_code = static::generateDSCode($deliverySchedule);
            }

            // Auto-fill product_id from job order if not set
            if ($deliverySchedule->job_order_id && !$deliverySchedule->product_id) {
                $jobOrder = $deliverySchedule->jobOrder;
                if ($jobOrder && $jobOrder->product_id) {
                    $deliverySchedule->product_id = $jobOrder->product_id;
                }
            }

            // Set max_quantity from quantity if not set
            if (empty($deliverySchedule->max_quantity)) {
                $deliverySchedule->max_quantity = $deliverySchedule->quantity;
            }

            // Auto-calculate week number from delivery_date
            if ($deliverySchedule->delivery_date && empty($deliverySchedule->week_number)) {
                $deliverySchedule->week_number = (string) $deliverySchedule->delivery_date->format('W');
            }

            // Fill jo_number and po_number from job order
            if ($deliverySchedule->job_order_id && !$deliverySchedule->jo_number) {
                $jobOrder = $deliverySchedule->jobOrder;
                if ($jobOrder) {
                    $deliverySchedule->jo_number = $jobOrder->jo_number;
                    $deliverySchedule->po_number = $jobOrder->po_number;
                }
            }

            // Auto-update status
            $deliverySchedule->updateDeliveryStatus();
        });

        static::updating(function (DeliverySchedule $deliverySchedule) {
            // Recalculate week_number if delivery_date changes
            if ($deliverySchedule->isDirty('delivery_date') && $deliverySchedule->delivery_date) {
                $deliverySchedule->week_number = (string) $deliverySchedule->delivery_date->format('W');
            }

            // Update status when delivery quantities change
            if ($deliverySchedule->isDirty(['delivered_quantity', 'fg_stocks'])) {
                $deliverySchedule->updateDeliveryStatus();
            }
        });
    }

    // ========== CODE GENERATION ==========
    
    public static function generateDSCode($model): string
    {
        // Use canonical DS format: DS-YYYY-####
        return static::nextDeliveryCode();
    }

    public static function nextDeliveryCode(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('ds_code', 'like', "DS-{$year}-%")
            ->orderBy('ds_code', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->ds_code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        
        return sprintf('DS-%d-%04d', $year, $nextNumber);
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getFulfillmentPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return ($this->delivered_quantity / $this->quantity) * 100;
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->delivered_quantity);
    }

    public function getBacklogQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->fg_stocks - $this->transfer_quantity);
    }

    public function getDaysUntilDeliveryAttribute(): ?int
    {
        if (!$this->delivery_date) {
            return null;
        }

        return now()->diffInDays($this->delivery_date, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->delivery_date) {
            return false;
        }
        
        return $this->delivery_date->isPast() && $this->ds_status !== 'DELIVERED';
    }

    public function getIsDelayedAttribute(): bool
    {
        if (!$this->delivery_date) {
            return false;
        }

        return $this->delivery_date->isPast() && $this->ds_status !== 'DELIVERED';
    }

    public function getHasSufficientStockAttribute(): bool
    {
        return $this->fg_stocks >= $this->remaining_quantity;
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return $this->ds_status;
    }
}