<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ptt_number',
        'job_order_id',
        'product_id',
        'section',
        'category',
        'status',
        'delivery_schedule_status',
        'date_transferred',
        'time_transferred',
        'date_delivery_scheduled',
        'week_number',
        'jit_days',
        'qty_transferred',
        'qty_jo_balance',
        'grade',
        'dimension',
        'unit_selling_price',
        'total_amount',
        'received_by_user_id',
        'date_received',
        'time_received',
        'qty_received',
        'remarks',
    ];

    protected $casts = [
        'date_transferred' => 'date',
        'date_delivery_scheduled' => 'date',
        'date_received' => 'date',
        'time_transferred' => 'datetime:H:i',
        'time_received' => 'datetime:H:i',
        'unit_selling_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'qty_transferred' => 'integer',
        'qty_jo_balance' => 'integer',
        'qty_received' => 'integer',
        'week_number' => 'integer',
        'jit_days' => 'integer',
    ];

    /**
     * Expose legacy attribute names for backward compatibility
     * (e.g., delivery_date => date_delivery_scheduled, jo_id => job_order_id)
     */
    protected $appends = ['delivery_date', 'jo_id'];

    // Relationships
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    // Scopes
    public function scopeBalance($query)
    {
        return $query->where('status', 'balance');
    }

    public function scopeComplete($query)
    {
        return $query->where('status', 'complete');
    }

    // Helper methods
    public function markComplete(): void
    {
        $this->update(['status' => 'complete']);
    }

    public function calculateTotalAmount(): void
    {
        $this->total_amount = $this->qty_received * $this->unit_selling_price;
        $this->save();
    }

    public function calculateJitDays(): void
    {
        if ($this->date_delivery_scheduled && $this->date_transferred) {
            $this->jit_days = Carbon::parse($this->date_delivery_scheduled)
                ->diffInDays($this->date_transferred);
            $this->save();
        }
    }

    // Backward-compatible attribute accessors / mutators
    // Allows legacy code to use ->delivery_date or ->jo_id
    public function getDeliveryDateAttribute()
    {
        return $this->date_delivery_scheduled;
    }

    public function setDeliveryDateAttribute($value): void
    {
        $this->attributes['date_delivery_scheduled'] = $value;
    }

    public function getJoIdAttribute()
    {
        return $this->job_order_id;
    }

    public function setJoIdAttribute($value): void
    {
        $this->attributes['job_order_id'] = $value;
    }

    // Boot method
    protected static function booted(): void
    {
        static::creating(function (Transfer $transfer) {
            // Auto-generate ptt_number: PTT-YYYY-NNNN
            if (empty($transfer->ptt_number)) {
                $year = Carbon::now()->year;

                $lastTransfer = static::where('ptt_number', 'like', "PTT-{$year}-%")
                    ->orderBy('ptt_number', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastTransfer) {
                    $parts = explode('-', $lastTransfer->ptt_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $transfer->ptt_number = sprintf("PTT-%d-%04d", $year, $nextNumber);
            }

            // Calculate total_amount if not provided
            if (empty($transfer->total_amount) && $transfer->qty_received && $transfer->unit_selling_price) {
                $transfer->total_amount = $transfer->qty_received * $transfer->unit_selling_price;
            }
        });
    }

    // Sequence helper
    public static function nextPttNumber(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('ptt_number', 'like', "PTT-{$year}-%")->orderBy('ptt_number', 'desc')->first();
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->ptt_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        return sprintf('PTT-%d-%04d', $year, $nextNumber);
    }
}