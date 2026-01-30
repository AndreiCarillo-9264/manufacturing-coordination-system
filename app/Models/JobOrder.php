<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'jo_number', 'status', 'jo_status', 'date_needed', 'po_number',
        'product_id', 'qty', 'uom', 'encoded_by_user_id', 'remarks',
        'jo_balance', 'ppqc_transfer', 'ds_quantity', 'withdrawal',
        'withdrawal_number', 'week_number', 'date_encoded', 'date_approved'
    ];

    protected $casts = [
        'date_needed' => 'date',
        'date_encoded' => 'date',
        'date_approved' => 'date',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function encodedBy()
    {
        return $this->belongsTo(User::class, 'encoded_by_user_id');
    }

    public function deliverySchedules()
    {
        return $this->hasMany(DeliverySchedule::class, 'jo_id');
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'jo_id');
    }

    // Accessors
    public function getJoBalanceAttribute($value)
    {
        // Calculate: qty - (ppqc_transfer + ds_quantity + withdrawal)
        return $this->qty - (
            ($this->ppqc_transfer ?? 0) +
            ($this->ds_quantity ?? 0) +
            ($this->withdrawal ?? 0)
        );
    }

    public function getTotalAmountAttribute()
    {
        return $this->qty * $this->product->selling_price;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Helper methods
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'date_approved' => now()
        ]);
    }

    public function markInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function calculateBalance()
    {
        // Calculate and save: qty - (ppqc_transfer + ds_quantity + withdrawal)
        $this->jo_balance = $this->qty - (
            ($this->ppqc_transfer ?? 0) +
            ($this->ds_quantity ?? 0) +
            ($this->withdrawal ?? 0)
        );
        $this->save();
    }

    // Boot method for auto-generation
    protected static function booted()
    {
        static::creating(function ($jobOrder) {
            // Auto-generate jo_number: JO-YYYY-NNNN
            if (empty($jobOrder->jo_number)) {
                $year = Carbon::now()->year;

                $lastJobOrder = static::where('jo_number', 'like', "JO-{$year}-%")
                    ->orderBy('jo_number', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastJobOrder) {
                    $parts = explode('-', $lastJobOrder->jo_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $jobOrder->jo_number = sprintf("JO-%d-%04d", $year, $nextNumber);
            }

            // Auto-generate po_number if not provided: PO-YYYY-NNNN
            if (empty($jobOrder->po_number)) {
                $year = Carbon::now()->year;
                $month = Carbon::now()->format('m');

                $lastPO = static::where('po_number', 'like', "PO-{$year}-{$month}-%")
                    ->orderBy('po_number', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastPO) {
                    $parts = explode('-', $lastPO->po_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $jobOrder->po_number = sprintf("PO-%d-%s-%04d", $year, $month, $nextNumber);
            }
        });
    }
}