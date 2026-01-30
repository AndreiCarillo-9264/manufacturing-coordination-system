<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ptt_number', 'section', 'date_transferred', 'jo_id', 'qty',
        'delivery_date', 'remarks', 'transfer_time', 'transfer_status',
        'jo_balance', 'product_id', 'grade', 'dimension',
        'received_by_user_id', 'date_received', 'time_received',
        'qty_received', 'jit_days', 'ds_status', 'week_num',
        'category', 'selling_price', 'total_amount'
    ];

    protected $casts = [
        'date_transferred' => 'date',
        'delivery_date' => 'date',
        'date_received' => 'date',
        'transfer_time' => 'datetime:H:i',
        'time_received' => 'datetime:H:i',
        'selling_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class, 'jo_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    // Accessors
    public function getTotalAmountAttribute()
    {
        return $this->qty_received * $this->selling_price;
    }

    public function getJitDaysAttribute()
    {
        return Carbon::parse($this->delivery_date)->diffInDays($this->date_transferred);
    }

    // Scopes
    public function scopeBalance($query)
    {
        return $query->where('transfer_status', 'balance');
    }

    public function scopeComplete($query)
    {
        return $query->where('transfer_status', 'complete');
    }

    // Helper method
    public function markComplete()
    {
        $this->update(['transfer_status' => 'complete']);
    }

    // Boot method for auto-generation
    protected static function booted()
    {
        static::creating(function ($transfer) {
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
        });
    }
}