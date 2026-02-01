<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class FinishedGood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'qty_beginning',
        'qty_in',
        'qty_out',
        'qty_theoretical_ending',
        'qty_actual_ending',
        'qty_variance',
        'qty_buffer_stock',
        'qty_pc_area',
        'amount_beginning',
        'amount_in',
        'amount_out',
        'amount_ending',
        'amount_variance',
        'date_last_in',
        'date_oldest',
        'days_aging',
        'aging_1_30_days',
        'aging_31_60_days',
        'aging_61_90_days',
        'aging_91_120_days',
        'aging_over_120_days',
        'remarks',
    ];

    protected $casts = [
        'date_last_in' => 'date',
        'date_oldest' => 'date',
        'qty_beginning' => 'integer',
        'qty_in' => 'integer',
        'qty_out' => 'integer',
        'qty_theoretical_ending' => 'integer',
        'qty_actual_ending' => 'integer',
        'qty_variance' => 'integer',
        'qty_buffer_stock' => 'integer',
        'qty_pc_area' => 'integer',
        'amount_beginning' => 'decimal:2',
        'amount_in' => 'decimal:2',
        'amount_out' => 'decimal:2',
        'amount_ending' => 'decimal:2',
        'amount_variance' => 'decimal:2',
        'days_aging' => 'integer',
        'aging_1_30_days' => 'integer',
        'aging_31_60_days' => 'integer',
        'aging_61_90_days' => 'integer',
        'aging_91_120_days' => 'integer',
        'aging_over_120_days' => 'integer',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function calculateTheoreticalEnding(): void
    {
        $this->qty_theoretical_ending = $this->qty_beginning + $this->qty_in - $this->qty_out;
        $this->save();
    }

    public function calculateVariance(): void
    {
        $this->qty_variance = $this->qty_actual_ending - $this->qty_theoretical_ending;
        $this->save();
    }

    public function calculateAmountEnding(): void
    {
        if ($this->product && $this->product->selling_price) {
            $this->amount_ending = $this->qty_actual_ending * $this->product->selling_price;
            $this->save();
        }
    }

    public function calculateAmountVariance(): void
    {
        if ($this->product && $this->product->selling_price) {
            $this->amount_variance = $this->qty_variance * $this->product->selling_price;
            $this->save();
        }
    }

    public function calculateDaysAging(): void
    {
        if ($this->date_last_in) {
            $this->days_aging = Carbon::parse($this->date_last_in)->diffInDays(now());
            $this->save();
        }
    }

    public function updateAgingRanges(): void
    {
        $this->calculateDaysAging();
        
        $days = $this->days_aging;
        $qty = $this->qty_actual_ending;

        // Reset all aging ranges
        $this->aging_1_30_days = 0;
        $this->aging_31_60_days = 0;
        $this->aging_61_90_days = 0;
        $this->aging_91_120_days = 0;
        $this->aging_over_120_days = 0;

        // Assign to appropriate range
        if ($days <= 30) {
            $this->aging_1_30_days = $qty;
        } elseif ($days <= 60) {
            $this->aging_31_60_days = $qty;
        } elseif ($days <= 90) {
            $this->aging_61_90_days = $qty;
        } elseif ($days <= 120) {
            $this->aging_91_120_days = $qty;
        } else {
            $this->aging_over_120_days = $qty;
        }

        $this->save();
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('qty_actual_ending', '<', 'qty_buffer_stock');
    }

    public function scopeWithVariance($query)
    {
        return $query->where('qty_variance', '!=', 0);
    }
}