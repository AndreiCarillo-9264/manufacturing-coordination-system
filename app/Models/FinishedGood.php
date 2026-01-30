<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class FinishedGood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'count_pc_area', 'beg', 'in_qty', 'out_qty',
        'theo_end', 'remarks', 'buffer_stocks', 'cur_sell_price',
        'beg_amt', 'in_amt', 'out_amt', 'end_amt', 'ending_count',
        'uom3', 'variance_count', 'variance_amount', 'last_in_date',
        'older_date', 'days', 'range_1_30', 'range_31_60',
        'range_61_90', 'range_91_120', 'range_over_120'
    ];

    protected $casts = [
        'last_in_date' => 'date',
        'older_date' => 'date',
        'cur_sell_price' => 'decimal:2',
        'beg_amt' => 'decimal:2',
        'in_amt' => 'decimal:2',
        'out_amt' => 'decimal:2',
        'end_amt' => 'decimal:2',
        'variance_amount' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors for calculated fields
    public function getTheoEndAttribute()
    {
        return $this->beg + $this->in_qty - $this->out_qty;
    }

    public function getEndAmtAttribute()
    {
        return $this->getTheoEndAttribute() * $this->cur_sell_price;
    }

    public function getVarianceCountAttribute()
    {
        return $this->ending_count - $this->getTheoEndAttribute();
    }

    public function getVarianceAmountAttribute()
    {
        return $this->getVarianceCountAttribute() * $this->cur_sell_price;
    }

    public function getDaysAttribute()
    {
        if (!$this->last_in_date) {
            return 0;
        }
        return Carbon::parse($this->last_in_date)->diffInDays(now());
    }

    // Helper method to update aging ranges
    public function updateAgingRanges()
    {
        $days = $this->getDaysAttribute();
        
        $this->range_1_30 = 0;
        $this->range_31_60 = 0;
        $this->range_61_90 = 0;
        $this->range_91_120 = 0;
        $this->range_over_120 = 0;

        $qty = $this->ending_count;

        if ($days <= 30) {
            $this->range_1_30 = $qty;
        } elseif ($days <= 60) {
            $this->range_31_60 = $qty;
        } elseif ($days <= 90) {
            $this->range_61_90 = $qty;
        } elseif ($days <= 120) {
            $this->range_91_120 = $qty;
        } else {
            $this->range_over_120 = $qty;
        }

        $this->save();
    }

    // Scope for low stock items
    public function scopeLowStock($query)
    {
        return $query->whereColumn('ending_count', '<', 'buffer_stocks');
    }
}