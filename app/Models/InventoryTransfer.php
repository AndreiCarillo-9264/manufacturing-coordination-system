<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Traits\AutoFillsFromProduct;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class InventoryTransfer extends Model
{
    use HasFactory, SoftDeletes, AutoFillsFromProduct, TracksUser, LogsActivity;

    protected $fillable = [
        'transfer_code',
        'ptt_number',
        'section',
        'status',
        'job_order_id',
        'product_id',
        
        // Auto-filled from product
        'product_code',
        'customer_name',
        'model_name',
        'description',
        'dimension',
        'grade',
        'uom',
        
        // Auto-filled from job order
        'jo_number',
        
        // Transfer specific
        'date_transferred',
        'time_transferred',
        'quantity',
        'jo_balance',
        'delivery_date',
        'transfer_by',
        'received_by_name',
        'received_by_user_id',
        'date_received',
        'time_received',
        'quantity_received',
        'jit',
        'days',
        'ds_status',
        'week_number',
        'category',
        'currency',
        'selling_price',
        'total_amount',
        'remarks',
        
        // Audit fields
        'encoded_by',
        'date_encoded',
        'updated_by',
    ];

    protected $casts = [
        'date_transferred' => 'date',
        'date_received' => 'date',
        'delivery_date' => 'date',
        'date_encoded' => 'datetime',
        'quantity' => 'integer',
        'jo_balance' => 'integer',
        'quantity_received' => 'integer',
        'days' => 'integer',
        'selling_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Backward compatibility
    protected $appends = ['delivery_date_scheduled', 'jo_id', 'qty_transferred', 'qty_received'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Inventory Transfer {$this->transfer_code} {$eventName}");
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

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    // ========== SCOPES ==========
    
    public function scopeBalance($query)
    {
        return $query->where('status', 'Balance');
    }

    public function scopeComplete($query)
    {
        return $query->where('status', 'Complete');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSection($query, $section)
    {
        return $query->where('section', $section);
    }

    public function scopeReceived($query)
    {
        return $query->whereNotNull('date_received');
    }

    public function scopeNotReceived($query)
    {
        return $query->whereNull('date_received');
    }

    public function scopeByWeek($query, $weekNumber)
    {
        return $query->where('week_number', $weekNumber);
    }

    // ========== HELPER METHODS ==========
    
    public function markComplete(): void
    {
        $this->update(['status' => 'Complete']);
    }

    public function markAsReceived(int $quantityReceived, $receivedBy = null, string $receivedByName = null): self
    {
        $this->quantity_received = $quantityReceived;
        
        // Handle both user ID (int) and user name (string)
        if (is_int($receivedBy)) {
            $this->received_by_user_id = $receivedBy;
        } elseif (is_string($receivedBy)) {
            $this->received_by_name = $receivedBy;
        }
        
        if ($receivedByName) {
            $this->received_by_name = $receivedByName;
        }
        
        $this->date_received = now();
        $this->time_received = now()->format('H:i:s');
        
        // Calculate days between transfer and receipt
        if ($this->date_transferred) {
            $this->days = $this->date_transferred->diffInDays($this->date_received);
        }
        
        // Update status
        if ($quantityReceived >= $this->quantity) {
            $this->status = 'Complete';
        } else {
            $this->status = 'Balance';
        }

        $this->save();

        return $this;
    }

    public function calculateTotalAmount(): void
    {
        if ($this->quantity && $this->selling_price) {
            $this->total_amount = $this->quantity * $this->selling_price;
            $this->save();
        }
    }

    public function calculateJitDays(): void
    {
        if ($this->delivery_date && $this->date_transferred) {
            $this->days = Carbon::parse($this->delivery_date)
                ->diffInDays($this->date_transferred);
            $this->save();
        }
    }

    public function isComplete(): bool
    {
        return $this->status === 'Complete';
    }

    public function hasVariance(): bool
    {
        return $this->quantity_received > 0 && $this->quantity_received !== $this->quantity;
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    // Old: date_delivery_scheduled -> New: delivery_date
    public function getDeliveryDateScheduledAttribute()
    {
        return $this->delivery_date;
    }

    public function setDeliveryDateScheduledAttribute($value): void
    {
        $this->attributes['delivery_date'] = $value;
    }

    // Old: job_order_id accessor
    public function getJoIdAttribute()
    {
        return $this->job_order_id;
    }

    public function setJoIdAttribute($value): void
    {
        $this->attributes['job_order_id'] = $value;
    }

    // Old: qty_transferred -> New: quantity
    public function getQtyTransferredAttribute()
    {
        return $this->quantity;
    }

    public function setQtyTransferredAttribute($value): void
    {
        $this->attributes['quantity'] = $value;
    }

    // Old: qty_received -> New: quantity_received
    public function getQtyReceivedAttribute()
    {
        return $this->quantity_received;
    }

    public function setQtyReceivedAttribute($value): void
    {
        $this->attributes['quantity_received'] = $value;
    }

    // Old: qty_jo_balance -> New: jo_balance
    public function getQtyJoBalanceAttribute()
    {
        return $this->jo_balance;
    }

    public function setQtyJoBalanceAttribute($value): void
    {
        $this->attributes['jo_balance'] = $value;
    }

    // Old: jit_days -> New: days
    public function getJitDaysAttribute()
    {
        return $this->days;
    }

    public function setJitDaysAttribute($value): void
    {
        $this->attributes['days'] = $value;
    }

    // Old: unit_selling_price -> New: selling_price
    public function getUnitSellingPriceAttribute()
    {
        return $this->selling_price;
    }

    public function setUnitSellingPriceAttribute($value): void
    {
        $this->attributes['selling_price'] = $value;
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (InventoryTransfer $transfer) {
            // Auto-generate transfer_code
            if (empty($transfer->transfer_code)) {
                $transfer->transfer_code = static::generateTransferCode($transfer);
            }

            // Auto-generate ptt_number: PTT-YYYY-NNNN
            if (empty($transfer->ptt_number)) {
                $transfer->ptt_number = static::generatePTTNumber();
            }

            // Calculate total_amount
            if ($transfer->quantity && $transfer->selling_price) {
                $transfer->total_amount = $transfer->quantity * $transfer->selling_price;
            }

            // Set transfer time if not set
            if (empty($transfer->time_transferred)) {
                $transfer->time_transferred = now()->format('H:i:s');
            }

            // Calculate week_number from date_transferred
            if ($transfer->date_transferred && empty($transfer->week_number)) {
                $transfer->week_number = (string) Carbon::parse($transfer->date_transferred)->format('W');
            }

            // Fill jo_number from job order
            if ($transfer->job_order_id && !$transfer->jo_number) {
                $jobOrder = $transfer->jobOrder;
                if ($jobOrder) {
                    $transfer->jo_number = $jobOrder->jo_number;
                }
            }

            // Auto-fill product_id from job order if not set
            if ($transfer->job_order_id && !$transfer->product_id) {
                $jobOrder = $transfer->jobOrder;
                if ($jobOrder && $jobOrder->product_id) {
                    $transfer->product_id = $jobOrder->product_id;
                }
            }
        });

        static::updating(function (InventoryTransfer $transfer) {
            // Recalculate total_amount if quantity or price changes
            if ($transfer->isDirty(['quantity', 'selling_price'])) {
                $transfer->total_amount = $transfer->quantity * $transfer->selling_price;
            }

            // Update status when quantity_received changes
            if ($transfer->isDirty('quantity_received') && $transfer->quantity_received > 0) {
                if ($transfer->quantity_received >= $transfer->quantity) {
                    $transfer->status = 'Complete';
                } else {
                    $transfer->status = 'Balance';
                }
            }

            // Calculate days if both dates are set
            if ($transfer->date_transferred && $transfer->date_received) {
                $transfer->days = $transfer->date_transferred->diffInDays($transfer->date_received);
            }
        });
    }

    // ========== CODE GENERATION ==========
    
    public static function generateTransferCode($model): string
    {
        $year = date('y');
        $productCode = $model->product_code ?? 'TRANS';
        $code = $year . 'C-' . substr($productCode, 0, 15);
        
        $count = static::where('transfer_code', 'LIKE', $code . '%')->count();
        
        return $code . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public static function generatePTTNumber(): string
    {
        $year = date('y');
        $prefix = 'PTT' . $year . '-';
        
        $lastPTT = static::where('ptt_number', 'LIKE', $prefix . '%')
            ->orderBy('ptt_number', 'desc')
            ->first();

        if ($lastPTT) {
            $lastNumber = (int) substr($lastPTT->ptt_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public static function nextPttNumber(): string
    {
        return static::generatePTTNumber();
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getVarianceQuantityAttribute(): int
    {
        return $this->quantity - ($this->quantity_received ?? 0);
    }

    public function getReceivalPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return (($this->quantity_received ?? 0) / $this->quantity) * 100;
    }

    public function getIsReceivedAttribute(): bool
    {
        return $this->date_received !== null;
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->quantity_received >= $this->quantity;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ?? 'Pending';
    }
}