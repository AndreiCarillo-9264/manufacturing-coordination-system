<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DeliverySchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_code',
        'job_order_id',
        'product_id',
        'status',
        'ppqc_status',
        'delivery_date',
        'week_number',
        'date_encoded',
        'qty_scheduled',
        'qty_delivered',
        'qty_transferred',
        'qty_max',
        'qty_fg_stocks',
        'qty_buffer_stock',
        'qty_backlog',
        'qty_jo_balance',
        'pmp_commitment',
        'ppqc_commitment',
        'remarks',
        'delivery_remarks',
        'jo_remarks',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'date_encoded' => 'date',
        'qty_scheduled' => 'integer',
        'qty_delivered' => 'integer',
        'qty_transferred' => 'integer',
        'qty_max' => 'integer',
        'qty_fg_stocks' => 'integer',
        'qty_buffer_stock' => 'integer',
        'qty_backlog' => 'integer',
        'qty_jo_balance' => 'integer',
        'week_number' => 'integer',
    ];

    // Relationships
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUrgent($query)
    {
        return $query->where('status', 'urgent');
    }

    public function scopeBacklog($query)
    {
        return $query->where('status', 'backlog');
    }

    public function scopeComplete($query)
    {
        return $query->where('status', 'complete');
    }

    public function scopeDelayed($query)
    {
        return $query->where('delivery_date', '<', now())
                     ->whereNotIn('status', ['complete']);
    }

    // Helper methods
    public function markUrgent(): void
    {
        $this->update(['status' => 'urgent']);
    }

    public function markComplete(): void
    {
        $this->update(['status' => 'complete']);
    }

    public function isDelayed(): bool
    {
        return $this->delivery_date->isPast() && $this->status !== 'complete';
    }

    public function calculateBacklog(): void
    {
        $this->qty_backlog = $this->qty_scheduled - (
            ($this->qty_fg_stocks ?? 0) +
            ($this->qty_transferred ?? 0)
        );
        $this->save();
    }

    // Backward-compatible accessor for legacy "jo_id" usage (maps to job_order_id)
    public function getJoIdAttribute()
    {
        return $this->job_order_id;
    }

    public function setJoIdAttribute($value): void
    {
        $this->attributes['job_order_id'] = $value;
    }

    // Backward-compatible alias for legacy "date" property -> delivery_date
    public function getDateAttribute()
    {
        return $this->delivery_date;
    }

    // Backward-compatible alias for legacy "qty" and "uom" properties
    public function getQtyAttribute()
    {
        return $this->qty_scheduled;
    }

    public function setQtyAttribute($value): void
    {
        $this->attributes['qty_scheduled'] = $value;
    }

    public function getUomAttribute()
    {
        return $this->product?->uom;
    }

    // Boot method
    protected static function booted(): void
    {
        static::creating(function (DeliverySchedule $deliverySchedule) {
            // Auto-generate delivery_code: DS-YYYY-NNNN
            if (empty($deliverySchedule->delivery_code)) {
                $year = Carbon::now()->year;

                $lastDelivery = static::where('delivery_code', 'like', "DS-{$year}-%")
                    ->orderBy('delivery_code', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastDelivery) {
                    $parts = explode('-', $lastDelivery->delivery_code);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $deliverySchedule->delivery_code = sprintf("DS-%d-%04d", $year, $nextNumber);
            }

            // Set date_encoded if not provided
            if (empty($deliverySchedule->date_encoded)) {
                $deliverySchedule->date_encoded = Carbon::today();
            }
        });
    }

    // Sequence helper
    public static function nextDeliveryCode(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('delivery_code', 'like', "DS-{$year}-%")->orderBy('delivery_code', 'desc')->first();
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->delivery_code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        return sprintf('DS-%d-%04d', $year, $nextNumber);
    }
}