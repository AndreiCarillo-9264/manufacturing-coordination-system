<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, SoftDeletes, TracksUser, LogsActivity;

    protected $fillable = [
        'product_code',
        'customer_name',
        'customer_location',
        'model_name',
        'description',
        'specs',
        'dimension',
        'moq',
        'uom',
        'currency',
        'selling_price',
        'rsqf_number',
        'po_remarks',
        'mc',
        'diff',
        'mu',
        'pc',
        'encoded_by',
        'date_encoded',
        'updated_by',
    ];

    protected $casts = [
        'moq' => 'integer',
        'selling_price' => 'decimal:2',
        'mc' => 'decimal:2',
        'diff' => 'decimal:2',
        'mu' => 'decimal:4',
        'date_encoded' => 'datetime',
    ];

    // Backward compatibility
    protected $appends = ['customer'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$this->product_code} {$eventName}");
    }

    // ========== RELATIONSHIPS ==========
    
    public function encodedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    public function inventoryTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class);
    }

    // Legacy relationship name
    public function transfers(): HasMany
    {
        return $this->inventoryTransfers();
    }

    public function endorseToLogistics(): HasMany
    {
        return $this->hasMany(EndorseToLogistic::class);
    }

    public function finishedGoods(): HasMany
    {
        return $this->hasMany(FinishedGood::class);
    }

    // Legacy singular relationship
    public function finishedGood(): HasOne
    {
        return $this->hasOne(FinishedGood::class);
    }

    public function actualInventory(): HasMany
    {
        return $this->hasMany(ActualInventory::class);
    }

    // Legacy relationship name
    public function actualInventories(): HasMany
    {
        return $this->actualInventory();
    }

    // ========== SCOPES ==========
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('product_code', 'LIKE', "%{$search}%")
              ->orWhere('customer_name', 'LIKE', "%{$search}%")
              ->orWhere('model_name', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    public function scopeCustomer($query, $customerName)
    {
        return $query->where('customer_name', 'LIKE', "%{$customerName}%");
    }

    public function scopeCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeWithLowStock($query, $threshold = 10)
    {
        return $query->whereHas('finishedGoods', function ($q) use ($threshold) {
            $q->where('current_qty', '<=', $threshold);
        });
    }

    // ========== HELPER METHODS ==========
    
    public function calculateMarkup(): self
    {
        if ($this->mc > 0) {
            $this->mu = ($this->selling_price - $this->mc) / $this->mc;
            $this->diff = $this->selling_price - $this->mc;
        }
        return $this;
    }

    public function updatePricing(float $sellingPrice, float $manufacturingCost = null): self
    {
        $this->selling_price = $sellingPrice;
        
        if ($manufacturingCost !== null) {
            $this->mc = $manufacturingCost;
        }
        
        $this->calculateMarkup();
        $this->save();
        
        return $this;
    }

    public function hasActiveJobOrders(): bool
    {
        return $this->jobOrders()
            ->whereIn('jo_status', ['Pending', 'Approved'])
            ->exists();
    }

    public function getAvailableStock(): int
    {
        return $this->finishedGoods()->sum('current_qty');
    }

    public function getTotalJobOrderQuantity(): int
    {
        return $this->jobOrders()->sum('quantity');
    }

    public function getPendingDeliveryQuantity(): int
    {
        return $this->deliverySchedules()
            ->where('ds_status', '!=', 'DELIVERED')
            ->sum('quantity');
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    // Old: customer -> New: customer_name
    public function getCustomerAttribute()
    {
        return $this->customer_name;
    }

    public function setCustomerAttribute($value): void
    {
        $this->attributes['customer_name'] = $value;
    }

    // Old: location -> New: customer_location (avoid conflict with inventory location)
    public function getLocationAttribute()
    {
        return $this->customer_location;
    }

    public function setLocationAttribute($value): void
    {
        $this->attributes['customer_location'] = $value;
    }

    // Legacy relationship accessor names
    public function encodedBy(): BelongsTo
    {
        return $this->encodedByUser();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->updatedByUser();
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            // Ensure product_code is uppercase
            if ($product->product_code) {
                $product->product_code = strtoupper($product->product_code);
            }

            // Calculate markup if not set
            if ($product->selling_price && $product->mc && !$product->mu) {
                $product->calculateMarkup();
            }
        });

        static::updating(function (Product $product) {
            // Ensure product_code is uppercase
            if ($product->isDirty('product_code')) {
                $product->product_code = strtoupper($product->product_code);
            }

            // Recalculate markup if selling_price or mc changes
            if ($product->isDirty(['selling_price', 'mc'])) {
                $product->calculateMarkup();
            }
        });

        // Auto-create FinishedGood when product is created
        static::created(function (Product $product) {
            // Only create if doesn't exist
            if (!$product->finishedGood) {
                // Ensure finished good has an encoded_by value to satisfy NOT NULL DB constraint
                $encodedBy = $product->encoded_by ?? (auth()->check() ? auth()->id() : 1);

                FinishedGood::create([
                    'product_id' => $product->id,
                    'encoded_by' => $encodedBy,
                ]);
            }
        });
    }

    // ========== CODE GENERATION ==========
    
    public static function nextProductCode(): string
    {
        $year = Carbon::now()->year;
        $last = static::where('product_code', 'like', "PRD-{$year}-%")
            ->orderBy('product_code', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->product_code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        
        return sprintf('PRD-%d-%04d', $year, $nextNumber);
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getTotalJobOrdersAttribute(): int
    {
        return $this->jobOrders()->count();
    }

    public function getTotalFinishedGoodsAttribute(): int
    {
        return $this->finishedGoods()->sum('current_qty');
    }

    public function getFormattedSellingPriceAttribute(): string
    {
        return number_format($this->selling_price, 2);
    }

    public function getMarkupPercentageAttribute(): ?float
    {
        if (!$this->mu) {
            return null;
        }
        
        return $this->mu * 100;
    }

    public function getHasStockAttribute(): bool
    {
        return $this->getAvailableStock() > 0;
    }

    public function getStockLevelAttribute(): string
    {
        $stock = $this->getAvailableStock();
        
        if ($stock === 0) {
            return 'Out of Stock';
        } elseif ($stock <= 10) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->product_code} - {$this->model_name}";
    }

    public function getFullDescriptionAttribute(): string
    {
        return "{$this->customer_name} | {$this->model_name} | {$this->description}";
    }
}