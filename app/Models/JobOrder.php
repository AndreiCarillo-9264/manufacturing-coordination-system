<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'jo_number',
        'po_number',
        'status',
        'fulfillment_status',
        'product_id',
        'qty_ordered',
        'qty_balance',
        'qty_transferred_to_ppqc',
        'qty_in_delivery_schedule',
        'withdrawal_status',
        'withdrawal_number',
        'week_number',
        'date_needed',
        'date_encoded',
        'date_approved',
        'encoded_by_user_id',
        'remarks',
    ];

    protected $casts = [
        'date_needed' => 'date',
        'date_encoded' => 'date',
        'date_approved' => 'date',
        'qty_ordered' => 'integer',
        'qty_balance' => 'integer',
        'qty_transferred_to_ppqc' => 'integer',
        'qty_in_delivery_schedule' => 'integer',
        'week_number' => 'integer',
    ];

    // Expose legacy attributes for backward compatibility: qty, uom
    protected $appends = ['qty','uom'];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by_user_id');
    }

    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
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
    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'date_approved' => now(),
        ]);
    }

    public function markInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function calculateBalance(): void
    {
        $this->qty_balance = $this->qty_ordered - (
            ($this->qty_transferred_to_ppqc ?? 0) +
            ($this->qty_in_delivery_schedule ?? 0)
        );
        $this->save();
    }

    // Backward-compatible aliases
    public function getQtyAttribute()
    {
        return $this->qty_ordered;
    }

    public function setQtyAttribute($value): void
    {
        $this->attributes['qty_ordered'] = $value;
    }

    public function getUomAttribute()
    {
        return $this->product?->uom;
    }

    public function setPoNumberAttribute($value): void
    {
        // Prevent accidentally setting PO to null/empty which would violate DB constraints
        if ($value === null || $value === '') {
            return;
        }
        $this->attributes['po_number'] = $value;
    }

    // Boot method
    protected static function booted(): void
    {
        static::creating(function (JobOrder $jobOrder) {
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

            // Set date_encoded if not provided
            if (empty($jobOrder->date_encoded)) {
                $jobOrder->date_encoded = Carbon::today();
            }

            // Set encoded_by if not provided
            if (empty($jobOrder->encoded_by_user_id) && auth()->check()) {
                $jobOrder->encoded_by_user_id = auth()->id();
            }

            // Auto-generate po_number: PO-YYYY-MM-NNNN
            if (empty($jobOrder->po_number)) {
                $year = Carbon::now()->year;
                $month = Carbon::now()->month;
                $prefix = sprintf('PO-%d-%02d-', $year, $month);

                $last = static::where('po_number', 'like', "{$prefix}%")
                    ->orderBy('po_number', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($last) {
                    $parts = explode('-', $last->po_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $jobOrder->po_number = sprintf("PO-%d-%02d-%04d", $year, $month, $nextNumber);
            }

            // Ensure week_number is set from date_needed if not provided (robust for programmatic creation)
            if (empty($jobOrder->week_number) && ! empty($jobOrder->date_needed)) {
                $jobOrder->week_number = (int) date('W', strtotime($jobOrder->date_needed));
            }
        });
    }

    // Sequence helpers (suggest next identifiers without creating records)
    public static function nextJoNumber(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('jo_number', 'like', "JO-{$year}-%")->orderBy('jo_number', 'desc')->first();
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->jo_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        return sprintf('JO-%d-%04d', $year, $nextNumber);
    }

    public static function nextPoNumber($date = null): string
    {
        $dt = $date ? Carbon::parse($date) : Carbon::now();
        $year = $dt->year;
        $month = $dt->month;
        $prefix = sprintf('PO-%d-%02d-', $year, $month);

        $last = static::where('po_number', 'like', "{$prefix}%")->orderBy('po_number', 'desc')->first();
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->po_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('PO-%d-%02d-%04d', $year, $month, $nextNumber);
    }
}