<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AutoFillsFromProduct;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class ActualInventory extends Model
{
    use HasFactory, SoftDeletes, AutoFillsFromProduct, TracksUser, LogsActivity;

    protected $table = 'actual_inventory';

    protected $fillable = [
        'tag_number',
        'product_id',
        'finished_good_id',
        
        // Auto-filled from product
        'product_code',
        'customer_name',
        'model_name',
        'description',
        'dimension',
        'uom',
        
        // Inventory count
        'fg_quantity',
        'location',
        
        // Count verification
        'counted_by',
        'counted_at',
        'verified_by',
        'verified_at',
        'status',
        
        'remarks',
        
        // Audit fields
        'encoded_by',
        'date_encoded',
        'updated_by',
    ];

    protected $casts = [
        'date_encoded' => 'datetime',
        'counted_at' => 'datetime',
        'verified_at' => 'datetime',
        'fg_quantity' => 'integer',
    ];

    // Backward compatibility
    protected $appends = ['qty_counted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Inventory Count {$this->tag_number} {$eventName}");
    }

    // ========== RELATIONSHIPS ==========
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function finishedGood(): BelongsTo
    {
        return $this->belongsTo(FinishedGood::class);
    }

    public function encodedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Note: counted_by and verified_by are now string names, not user IDs
    // For backward compatibility, we can add methods to find users by name
    public function getCountedByUserAttribute()
    {
        if (!$this->counted_by) {
            return null;
        }
        return User::where('name', $this->counted_by)->first();
    }

    public function getVerifiedByUserAttribute()
    {
        if (!$this->verified_by) {
            return null;
        }
        return User::where('name', $this->verified_by)->first();
    }

    // ========== SCOPES ==========
    
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeCounted($query)
    {
        return $query->where('status', 'Counted');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'Verified');
    }

    public function scopeDiscrepancies($query)
    {
        return $query->where('status', 'Discrepancy');
    }

    public function scopeLocation($query, $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    // ========== HELPER METHODS ==========
    
    public function markAsCounted(string $countedBy): self
    {
        $this->counted_by = $countedBy;
        $this->counted_at = now();
        $this->status = 'Counted';
        $this->save();

        return $this;
    }

    public function markAsVerified(string $verifiedBy): self
    {
        // Allow verification regardless of current status
        // If counted_at is set but status is still pending, mark as counted first
        if ($this->status === 'Pending' && !is_null($this->counted_at)) {
            $this->status = 'Counted';
        }

        $this->verified_by = $verifiedBy;
        $this->verified_at = \Illuminate\Support\Carbon::now();
        $this->status = 'Verified';

        // UPDATE FINISHED GOODS FIRST (must happen before discrepancy check)
        $this->updateFinishedGoodsSimple();

        // THEN check for discrepancy with the UPDATED finished goods
        try {
            $fgCurrentQty = \Illuminate\Support\Facades\DB::table('finished_goods')
                ->where('product_id', $this->product_id)
                ->value('current_qty');

            // If quantities don't match after update, it's a discrepancy
            if ($fgCurrentQty !== null && $this->fg_quantity !== $fgCurrentQty) {
                $this->status = 'Discrepancy';
            }
        } catch (\Exception $e) {
            // Log but don't fail if there's an issue with finished good lookup
            \Illuminate\Support\Facades\Log::warning('Error checking finished good discrepancy: ' . $e->getMessage());
        }

        // Save the inventory record with final status
        $this->save();

        // Create endorsement to logistics asynchronously (doesn't block the response)
        // Use a deferred callback so it runs after response is sent
        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->createEndorsementToLogistics();
        });

        return $this;
    }

    private function updateFinishedGoodsSimple(): void
    {
        try {
            // Use updateOrInsert for maximum performance
            // This is faster than checking first then updating
            \Illuminate\Support\Facades\DB::table('finished_goods')->updateOrInsert(
                // Search condition
                ['product_id' => $this->product_id],
                // Update values (or insert if not found)
                [
                    'current_qty' => $this->fg_quantity,
                    'updated_by' => \Illuminate\Support\Facades\Auth::id(),
                    'updated_at' => now(),
                    // These only used on insert
                    'fg_code' => 'FG-' . \Illuminate\Support\Str::uuid(),
                    'product_code' => $this->product_code,
                    'customer_name' => $this->customer_name,
                    'model_name' => $this->model_name,
                    'description' => $this->description,
                    'uom' => $this->uom ?? 'PC/S',
                    'buffer_stocks' => 500, // Default buffer stock for stock health calculations
                    'encoded_by' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    'created_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            // Log but don't fail - verification should still succeed
            \Illuminate\Support\Facades\Log::warning('Error updating finished goods after verification: ' . $e->getMessage(), [
                'inventory_id' => $this->id,
            ]);
        }
    }

    private function createEndorsementToLogistics(): void
    {
        try {
            // Check if auto-creation is enabled (can be disabled via env or feature flag)
            if (!config('app.auto_create_etl_on_verification', true)) {
                return; // Skip ETL creation if disabled
            }

            // Use a simple check-and-insert pattern for speed
            $existingCount = \Illuminate\Support\Facades\DB::table('endorse_to_logistics')
                ->where('product_id', $this->product_id)
                ->where('status', 'pending')
                ->count();

            // Only create if one doesn't already exist
            if ($existingCount === 0) {
                // Create temp model to generate proper ETL code
                $tempModel = new \App\Models\EndorseToLogistic([
                    'product_id' => $this->product_id,
                    'product_code' => $this->product_code,
                ]);
                
                // Use raw insert for maximum speed but with proper ETL code generation
                \Illuminate\Support\Facades\DB::table('endorse_to_logistics')->insert([
                    'etl_code' => \App\Models\EndorseToLogistic::generateETLCode($tempModel),
                    'product_id' => $this->product_id,
                    'product_code' => $this->product_code,
                    'customer_name' => $this->customer_name,
                    'model_name' => $this->model_name,
                    'description' => $this->description,
                    'uom' => $this->uom ?? 'PC/S',
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'total_out' => $this->fg_quantity,
                    'quantity' => $this->fg_quantity,
                    'status' => 'pending',
                    'encoded_by' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    'date_encoded' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \Illuminate\Support\Facades\Log::info('Created endorsement to logistics', [
                    'inventory_id' => $this->id,
                    'product_id' => $this->product_id,
                ]);
            }
        } catch (\Exception $e) {
            // Log but don't fail - verification should still succeed
            \Illuminate\Support\Facades\Log::warning('Error creating endorsement to logistics: ' . $e->getMessage(), [
                'inventory_id' => $this->id,
            ]);
        }
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function isCounted(): bool
    {
        return !is_null($this->counted_at);
    }

    public function hasDiscrepancy(): bool
    {
        $variance = $this->variance;
        return $variance !== null && $variance !== 0;
    }

    public function resolveDiscrepancy(int $adjustedQuantity, string $resolvedBy, string $reason): self
    {
        if ($this->status !== 'Discrepancy') {
            throw new \Exception("Only discrepancies can be resolved");
        }

        // Update finished good stock
        if ($this->finishedGood) {
            $this->finishedGood->adjustStock($adjustedQuantity, "Resolved by {$resolvedBy}: {$reason}");
        }

        // Update inventory count
        $this->fg_quantity = $adjustedQuantity;
        $this->status = 'Verified';
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . 
                        "Discrepancy resolved by {$resolvedBy}: {$reason}";
        $this->save();

        return $this;
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    // Old: qty_counted -> New: fg_quantity
    public function getQtyCountedAttribute()
    {
        return $this->fg_quantity;
    }

    public function setQtyCountedAttribute($value): void
    {
        $this->attributes['fg_quantity'] = $value;
    }

    // Legacy method names for marking counted/verified
    public function markCounted(int $userId): void
    {
        // In new version, we use name instead of user ID
        $user = User::find($userId);
        if ($user) {
            $this->markAsCounted($user->name);
        }
    }

    public function markVerified(int $userId): void
    {
        // In new version, we use name instead of user ID
        $user = User::find($userId);
        if ($user) {
            $this->markAsVerified($user->name);
        }
    }

    // Legacy relationship accessors (no longer direct user relationships)
    public function countedBy(): ?BelongsTo
    {
        return null; // Not a direct relationship anymore
    }

    public function verifiedBy(): ?BelongsTo
    {
        return null; // Not a direct relationship anymore
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (ActualInventory $model) {
            // Auto-generate tag number
            if (empty($model->tag_number)) {
                $model->tag_number = static::generateTagNumber();
            }

            // Set default status
            if (empty($model->status)) {
                $model->status = 'Pending';
            }

            // Link to finished good if exists
            if ($model->product_id && !$model->finished_good_id) {
                $finishedGood = FinishedGood::where('product_id', $model->product_id)->first();
                if ($finishedGood) {
                    $model->finished_good_id = $finishedGood->id;
                }
            }
        });

        static::updated(function (ActualInventory $model) {
            // Auto-check for discrepancy when verified
            if ($model->isDirty('verified_at') && $model->verified_at) {
                if ($model->finishedGood && $model->fg_quantity !== $model->finishedGood->current_qty) {
                    $model->status = 'Discrepancy';
                    $model->save();
                }
            }
        });
    }

    // ========== CODE GENERATION ==========
    
    public static function generateTagNumber(): string
    {
        $year = date('Y');
        $prefix = 'TAG-' . $year . '-';
        
        $lastTag = static::where('tag_number', 'LIKE', $prefix . '%')
            ->orderBy('tag_number', 'desc')
            ->first();

        if ($lastTag) {
            $lastNumber = (int) substr($lastTag->tag_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function nextTagNumber(): string
    {
        return static::generateTagNumber();
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getVarianceAttribute(): ?int
    {
        if (!$this->finished_good_id || !$this->finishedGood) {
            return null;
        }

        return $this->fg_quantity - $this->finishedGood->current_qty;
    }

    public function getDiscrepancyPercentageAttribute(): ?float
    {
        if (!$this->finished_good_id || !$this->finishedGood) {
            return null;
        }

        $systemQty = $this->finishedGood->current_qty;
        if ($systemQty === 0) {
            return null;
        }

        $variance = $this->variance;
        return ($variance / $systemQty) * 100;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ?? 'Pending';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Verified' => 'green',
            'Counted' => 'blue',
            'Discrepancy' => 'red',
            'Pending' => 'yellow',
            default => 'gray',
        };
    }

    public function getSystemQuantityAttribute(): ?int
    {
        return $this->finishedGood?->current_qty;
    }

    public function getIsAccurateAttribute(): bool
    {
        if (!$this->finishedGood) {
            return true;
        }

        return $this->fg_quantity === $this->finishedGood->current_qty;
    }
}