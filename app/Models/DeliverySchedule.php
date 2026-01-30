<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DeliverySchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ds_delivery_code', 'ds_status', 'date', 'jo_id', 'po_number',
        'product_id', 'qty', 'uom', 'remarks', 'pmp_commitment',
        'ppqc_commitment', 'fg_stocks', 'status', 'delivery_remarks',
        'jo_remarks', 'ppqc_status', 'jo_balance', 'transfer',
        'delivered_dsd', 'ds_qty', 'week_num', 'date_encoded',
        'max_qty', 'buffer_stocks', 'backlog'
    ];

    protected $casts = [
        'date' => 'date',
        'date_encoded' => 'date',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessor for backlog calculation
    public function getBacklogAttribute()
    {
        return $this->qty - (($this->fg_stocks ?? 0) + ($this->transfer ?? 0));
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('ds_status', 'pending');
    }

    public function scopeUrgent($query)
    {
        return $query->where('ds_status', 'urgent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('ds_status', 'delivered');
    }

    public function scopeDelayed($query)
    {
        return $query->where('date', '<', now())
                     ->whereNotIn('ds_status', ['delivered']);
    }

    // Helper methods
    public function markUrgent()
    {
        $this->update(['ds_status' => 'urgent']);
    }

    public function markDelivered()
    {
        $this->update(['ds_status' => 'delivered']);
    }

    public function isDelayed(): bool
    {
        return $this->date->isPast() && $this->ds_status !== 'delivered';
    }

    // Boot method for auto-generation
    protected static function booted()
    {
        static::creating(function ($deliverySchedule) {
            // Auto-generate ds_delivery_code: DS-YYYY-NNNN
            if (empty($deliverySchedule->ds_delivery_code)) {
                $year = Carbon::now()->year;

                $lastDelivery = static::where('ds_delivery_code', 'like', "DS-{$year}-%")
                    ->orderBy('ds_delivery_code', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastDelivery) {
                    $parts = explode('-', $lastDelivery->ds_delivery_code);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $deliverySchedule->ds_delivery_code = sprintf("DS-%d-%04d", $year, $nextNumber);
            }
        });
    }
}