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

class FinishedGood extends Model
{
    use HasFactory, SoftDeletes, AutoFillsFromProduct, TracksUser, LogsActivity;

    protected $fillable = [
        'fg_code',
        'count',
        'pc',
        'area',
        'product_id',
        'job_order_id',
        
        // Auto-filled from product
        'product_code',
        'customer_name',
        'model_name',
        'description',
        'dimension',
        'uom',
        
        // Quantity fields
        'beginning_qty',
        'in_qty',
        'out_qty',
        'theoretical_end_qty',
        'buffer_stocks',
        'current_qty',
        'ending_count',
        'uom3',
        'variance_amount',
        'variance_qty',
        
        // Pricing fields
        'currency',
        'selling_price',
        'beginning_amount',
        'in_amount',
        'out_amount',
        'end_amount',
        
        // Age tracking
        'last_in_date',
        'older_date',
        'number_of_days',
        'range',
        'age_1_30_days',
        'age_31_60_days',
        'age_61_90_days',
        'age_91_120_days',
        'age_over_120_days',
        
        'remarks',
        
        // Audit fields
        'encoded_by',
        'date_encoded',
        'updated_by',
    ];

    protected $casts = [
        'date_encoded' => 'datetime',
        'last_in_date' => 'date',
        'older_date' => 'date',
        'count' => 'integer',
        'beginning_qty' => 'integer',
        'in_qty' => 'integer',
        'out_qty' => 'integer',
        'theoretical_end_qty' => 'integer',
        'buffer_stocks' => 'integer',
        'current_qty' => 'integer',
        'ending_count' => 'integer',
        'variance_qty' => 'integer',
        'number_of_days' => 'integer',
        'age_1_30_days' => 'integer',
        'age_31_60_days' => 'integer',
        'age_61_90_days' => 'integer',
        'age_91_120_days' => 'integer',
        'age_over_120_days' => 'integer',
        'selling_price' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'beginning_amount' => 'decimal:2',
        'in_amount' => 'decimal:2',
        'out_amount' => 'decimal:2',
        'end_amount' => 'decimal:2',
    ];

    // Backward compatibility
    protected $appends = [
        'qty_beginning', 'qty_in', 'qty_out', 
        'qty_theoretical_ending', 'qty_actual_ending',
        'qty_variance', 'qty_buffer_stock',
        'date_last_in', 'days_aging'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Finished Good {$this->fg_code} {$eventName}");
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

    public function actualInventory(): HasMany
    {
        return $this->hasMany(ActualInventory::class);
    }

    // ========== SCOPES ==========
    
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('current_qty', '<=', $threshold);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_qty', 0);
    }

    public function scopeWithVariance($query)
    {
        return $query->whereRaw('current_qty != theoretical_end_qty');
    }

    public function scopeOldStock($query, $days = 90)
    {
        return $query->where('number_of_days', '>', $days);
    }

    public function scopeArea($query, $area)
    {
        return $query->where('area', $area);
    }

    public function scopeBelowBufferStock($query)
    {
        return $query->whereColumn('current_qty', '<', 'buffer_stocks');
    }

    // ========== HELPER METHODS ==========
    
    public function calculateTheoreticalEnd(): self
    {
        $this->theoretical_end_qty = $this->beginning_qty + $this->in_qty - $this->out_qty;
        return $this;
    }

    public function calculateAmounts(): self
    {
        if (!$this->selling_price) {
            return $this;
        }

        $this->beginning_amount = $this->beginning_qty * $this->selling_price;
        $this->in_amount = $this->in_qty * $this->selling_price;
        $this->out_amount = $this->out_qty * $this->selling_price;
        $this->end_amount = $this->theoretical_end_qty * $this->selling_price;

        return $this;
    }

    public function calculateVariance(): self
    {
        $this->variance_qty = $this->current_qty - $this->theoretical_end_qty;
        
        if ($this->selling_price) {
            $this->variance_amount = $this->variance_qty * $this->selling_price;
        }

        return $this;
    }

    public function calculateAgeRanges(): self
    {
        if (!$this->last_in_date) {
            return $this;
        }

        $days = now()->diffInDays($this->last_in_date);
        $this->number_of_days = $days;

        // Reset all age ranges
        $this->age_1_30_days = 0;
        $this->age_31_60_days = 0;
        $this->age_61_90_days = 0;
        $this->age_91_120_days = 0;
        $this->age_over_120_days = 0;

        // Assign quantity to appropriate age range
        if ($days <= 30) {
            $this->range = '1-30';
            $this->age_1_30_days = $this->current_qty;
        } elseif ($days <= 60) {
            $this->range = '31-60';
            $this->age_31_60_days = $this->current_qty;
        } elseif ($days <= 90) {
            $this->range = '61-90';
            $this->age_61_90_days = $this->current_qty;
        } elseif ($days <= 120) {
            $this->range = '91-120';
            $this->age_91_120_days = $this->current_qty;
        } else {
            $this->range = 'OVER 120';
            $this->age_over_120_days = $this->current_qty;
        }

        return $this;
    }

    public function addStock(int $quantity): self
    {
        $this->in_qty += $quantity;
        $this->current_qty += $quantity;
        $this->last_in_date = now();
        
        $this->calculateTheoreticalEnd();
        $this->calculateAmounts();
        $this->calculateAgeRanges();
        
        $this->save();

        return $this;
    }

    public function removeStock(int $quantity, ?string $justification = null): self
    {
        if ($quantity > $this->current_qty) {
            throw new \Exception("Insufficient stock. Available: {$this->current_qty}, Requested: {$quantity}");
        }

        $wouldBe = $this->current_qty - $quantity;

        // If removing this quantity will bring stock below buffer, require justification
        if (!is_null($this->buffer_stocks) && $wouldBe < $this->buffer_stocks && empty($justification)) {
            throw new \Exception("Removing this quantity will bring stock below buffer ({$this->buffer_stocks}). Justification required.");
        }

        $this->out_qty += $quantity;
        $this->current_qty -= $quantity;
        
        $this->calculateTheoreticalEnd();
        $this->calculateAmounts();
        $this->calculateVariance();
        
        // If justification provided, record it in remarks
        if ($justification) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') .
                             "Stock removed ({$quantity}) justification: {$justification}";
        }

        $this->save();

        return $this;
    }

    public function adjustStock(int $newQuantity, string $reason = null): self
    {
        $oldQuantity = $this->current_qty;
        $this->current_qty = $newQuantity;
        
        $this->calculateVariance();
        
        if ($reason) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . 
                            "Stock adjusted from {$oldQuantity} to {$newQuantity}: {$reason}";
        }
        
        $this->save();

        return $this;
    }

    public function hasVariance(): bool
    {
        return $this->current_qty !== $this->theoretical_end_qty;
    }

    public function isOldStock($days = 90): bool
    {
        return $this->number_of_days > $days;
    }

    public function isBelowBufferStock(): bool
    {
        return $this->current_qty < $this->buffer_stocks;
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    public function getQtyBeginningAttribute()
    {
        return $this->beginning_qty;
    }

    public function getQtyInAttribute()
    {
        return $this->in_qty;
    }

    public function getQtyOutAttribute()
    {
        return $this->out_qty;
    }

    public function getQtyTheoreticalEndingAttribute()
    {
        return $this->theoretical_end_qty;
    }

    public function getQtyActualEndingAttribute()
    {
        return $this->current_qty;
    }

    public function getQtyVarianceAttribute()
    {
        return $this->variance_qty;
    }

    public function getQtyBufferStockAttribute()
    {
        return $this->buffer_stocks;
    }

    public function getDateLastInAttribute()
    {
        return $this->last_in_date;
    }

    public function getDaysAgingAttribute()
    {
        return $this->number_of_days;
    }

    // Legacy relationship names
    public function encodedBy(): BelongsTo
    {
        return $this->encodedByUser();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->updatedByUser();
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (FinishedGood $model) {
            // Auto-generate FG code
            if (empty($model->fg_code)) {
                $model->fg_code = static::generateFGCode();
            }

            // Calculate theoretical end quantity
            $model->calculateTheoreticalEnd();

            // Calculate amounts if selling price is set
            if ($model->selling_price) {
                $model->calculateAmounts();
            }

            // Set last_in_date
            if ($model->in_qty > 0 && empty($model->last_in_date)) {
                $model->last_in_date = now();
            }

            // Get selling price from product if not set
            if (!$model->selling_price && $model->product) {
                $model->selling_price = $model->product->selling_price;
                $model->currency = $model->product->currency;
            }
        });

        static::updating(function (FinishedGood $model) {
            // Recalculate theoretical end
            if ($model->isDirty(['beginning_qty', 'in_qty', 'out_qty'])) {
                $model->calculateTheoreticalEnd();
            }

            // Recalculate amounts
            if ($model->isDirty(['beginning_qty', 'in_qty', 'out_qty', 'selling_price'])) {
                $model->calculateAmounts();
            }

            // Update last_in_date when receiving stock
            if ($model->isDirty('in_qty') && $model->in_qty > $model->getOriginal('in_qty')) {
                $model->last_in_date = now();
            }

            // Calculate age ranges
            if ($model->isDirty('last_in_date') || $model->isDirty('current_qty')) {
                $model->calculateAgeRanges();
            }

            // Calculate variance
            if ($model->isDirty('current_qty')) {
                $model->calculateVariance();
            }
        });
    }

    // ========== CODE GENERATION ==========
    
    public static function generateFGCode(): string
    {
        $year = date('Y');
        $prefix = 'FG-' . $year . '-';
        
        $lastFG = static::where('fg_code', 'LIKE', $prefix . '%')
            ->orderBy('fg_code', 'desc')
            ->first();

        if ($lastFG) {
            $lastNumber = (int) substr($lastFG->fg_code, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getStockStatusAttribute(): string
    {
        if ($this->current_qty === 0) {
            return 'Out of Stock';
        }

        if ($this->current_qty <= 10) {
            return 'Low Stock';
        }

        if ($this->number_of_days > 90) {
            return 'Old Stock';
        }

        if ($this->isBelowBufferStock()) {
            return 'Below Buffer';
        }

        return 'In Stock';
    }

    public function getVariancePercentageAttribute(): ?float
    {
        if ($this->theoretical_end_qty == 0) {
            return null;
        }

        return ($this->variance_qty / $this->theoretical_end_qty) * 100;
    }

    public function getStockTurnoverAttribute(): ?float
    {
        if ($this->beginning_qty == 0) {
            return null;
        }

        return $this->out_qty / $this->beginning_qty;
    }

    public function getAgeRangeLabelAttribute(): string
    {
        return $this->range ?? 'Unknown';
    }

    public function getDaysUntilExpiredAttribute(): ?int
    {
        // Assuming 120 days is "expired"
        if (!$this->number_of_days) {
            return null;
        }

        return max(0, 120 - $this->number_of_days);
    }
}