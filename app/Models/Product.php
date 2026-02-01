<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_code',
        'model_name',
        'description',
        'customer',
        'specs',
        'dimension',
        'location',
        'pc',
        'uom',
        'moq',
        'currency',
        'selling_price',
        'mc',
        'diff',
        'mu',
        'rsqf_number',
        'remarks',
        'encoded_by_user_id',
        'date_encoded',
    ];

    protected $casts = [
        'date_encoded' => 'date',
        'selling_price' => 'decimal:2',
        'mc' => 'decimal:2',
        'diff' => 'decimal:2',
        'mu' => 'decimal:2',
        'moq' => 'integer',
    ];

    // Relationships
    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by_user_id');
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function finishedGood(): HasOne
    {
        return $this->hasOne(FinishedGood::class);
    }

    public function actualInventories(): HasMany
    {
        return $this->hasMany(ActualInventory::class);
    }

    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    // Boot method
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            // Auto-generate product_code: PRD-YYYY-NNNN
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

            // Auto set date_encoded
            if (empty($product->date_encoded)) {
                $product->date_encoded = Carbon::today();
            }

            // Auto set encoded_by
            if (empty($product->encoded_by_user_id) && auth()->check()) {
                $product->encoded_by_user_id = auth()->id();
            }
        });

        // Auto-create related FinishedGood record
        static::created(function (Product $product) {
            FinishedGood::create([
                'product_id' => $product->id,
            ]);
        });
    }

    // Sequence helper
    public static function nextProductCode(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('product_code', 'like', "PRD-{$year}-%")->orderBy('product_code', 'desc')->first();
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->product_code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        return sprintf('PRD-%d-%04d', $year, $nextNumber);
    }
}