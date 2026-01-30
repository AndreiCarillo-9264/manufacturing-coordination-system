<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer',
        'encoded_by_user_id',
        'product_code',
        'model_name',
        'description',
        'date_encoded',
        'specs',
        'dimension',
        'moq',
        'uom',
        'currency',
        'selling_price',
        'rsqf_number',
        'remarks_po',
        'mc',
        'diff',
        'mu',
        'location',
        'pc',
    ];

    protected $casts = [
        'date_encoded'   => 'date',
        'selling_price'  => 'decimal:2',
        'mc'             => 'decimal:2',
        'diff'           => 'decimal:2',
        'mu'             => 'decimal:2',
    ];

    /**
     * Accessors to include calculated fields in array/JSON output
     */
    protected $appends = [];

    // Relationships
    public function encodedBy()
    {
        return $this->belongsTo(User::class, 'encoded_by_user_id');
    }

    public function jobOrders()
    {
        return $this->hasMany(JobOrder::class);
    }

    public function finishedGood()
    {
        return $this->hasOne(FinishedGood::class);
    }

    public function actualInventories()
    {
        return $this->hasMany(ActualInventory::class);
    }

    public function deliverySchedules()
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    /**
     * Boot method – auto-generation and defaults
     */
    protected static function booted()
    {
        // Auto-create related FinishedGood record
        static::created(function ($product) {
            FinishedGood::create([
                'product_id'     => $product->id,
                'cur_sell_price' => $product->selling_price ?? 0,
            ]);
        });

        // Auto-generate product_code, date_encoded, encoded_by before create
        static::creating(function ($product) {
            // Auto product_code: PRD-YYYY-NNNN
            if (empty($product->product_code)) {
                $year = Carbon::now()->year;

                $lastProduct = static::where('product_code', 'like', "PRD-{$year}-%")
                    ->orderBy('product_code', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastProduct) {
                    $parts = explode('-', $lastProduct->product_code);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }

                $product->product_code = sprintf("PRD-%d-%04d", $year, $nextNumber);
            }

            // Auto date_encoded = today
            if (empty($product->date_encoded)) {
                $product->date_encoded = Carbon::today();
            }

            // Auto encoded_by = current user
            if (auth()->check()) {
                $product->encoded_by_user_id = auth()->id();
            }
        });
    }
}