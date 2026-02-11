<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AutoFillsFromProduct;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class EndorseToLogistic extends Model
{
    use HasFactory, SoftDeletes, AutoFillsFromProduct, TracksUser, LogsActivity;

    protected $table = 'endorse_to_logistics';

    protected $fillable = [
        'etl_code',
        'product_id',
        'delivery_schedule_id',
        
        // Auto-filled from product
        'product_code',
        'customer_name',
        'model_name',
        'description',
        'uom',
        
        // Endorsement details
        'date',
        'time',
        'total_out',
        'quantity',
        'quantity_delivered',
        'delivery_date',
        'dr_number',
        'si_number',
        'received_by',
        'date_received',
        'stretch_film_code',
        'remarks',
        
        // Workflow status
        'status',
        'approved_at',
        'approved_by',
        'completed_at',
        'completed_by',
        
        // Audit fields
        'encoded_by',
        'date_encoded',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'delivery_date' => 'date',
        'date_received' => 'date',
        'date_encoded' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_out' => 'integer',
        'quantity' => 'integer',
        'quantity_delivered' => 'integer',
    ];

    // Backward compatibility
    protected $appends = [
        'etl_delivery_code', 'date_endorsed', 'time_endorsed',
        'qty_delivered', 'common_stretch_film_code', 'customer', 'model'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "ETL {$this->etl_code} {$eventName}");
    }

    // ========== RELATIONSHIPS ==========
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function deliverySchedule(): BelongsTo
    {
        return $this->belongsTo(DeliverySchedule::class);
    }

    public function encodedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Legacy - job_order_id was in old structure
    public function jobOrder(): ?BelongsTo
    {
        // Get job order through delivery schedule
        return $this->deliverySchedule?->jobOrder();
    }

    // ========== SCOPES ==========
    
    public function scopeDelivered($query)
    {
        return $query->whereNotNull('date_received');
    }

    public function scopePending($query)
    {
        return $query->whereNull('date_received');
    }

    public function scopeWithDR($query)
    {
        return $query->whereNotNull('dr_number');
    }

    public function scopeWithSI($query)
    {
        return $query->whereNotNull('si_number');
    }

    public function scopeOverdue($query)
    {
        return $query->where('delivery_date', '<', now())
                     ->whereNull('date_received');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('delivery_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('delivery_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // ========== HELPER METHODS ==========
    
    public function markAsReceived(string $receivedBy, string $drNumber = null, string $siNumber = null): self
    {
        $this->received_by = $receivedBy;
        $this->date_received = now();
        $this->quantity_delivered = $this->quantity;
        
        if ($drNumber) {
            $this->dr_number = $drNumber;
        }
        
        if ($siNumber) {
            $this->si_number = $siNumber;
        }

        $this->save();

        return $this;
    }

    public function updateDeliveryDocuments(string $drNumber = null, string $siNumber = null): self
    {
        if ($drNumber) {
            $this->dr_number = $drNumber;
        }
        
        if ($siNumber) {
            $this->si_number = $siNumber;
        }

        $this->save();

        return $this;
    }

    public function isDelivered(): bool
    {
        return $this->date_received !== null;
    }

    public function isOverdue(): bool
    {
        return !$this->isDelivered() && 
               $this->delivery_date && 
               $this->delivery_date < now();
    }

    public function getIsDeliveredAttribute(): bool
    {
        return $this->date_received !== null;
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->date_received && 
               $this->delivery_date && 
               $this->delivery_date < now();
    }

    // ========== BACKWARD COMPATIBILITY ACCESSORS ==========
    
    // Old: etl_delivery_code -> New: etl_code
    public function getEtlDeliveryCodeAttribute()
    {
        return $this->etl_code;
    }

    public function setEtlDeliveryCodeAttribute($value): void
    {
        $this->attributes['etl_code'] = $value;
    }

    // Old: date_endorsed -> New: date
    public function getDateEndorsedAttribute()
    {
        return $this->date;
    }

    public function setDateEndorsedAttribute($value): void
    {
        $this->attributes['date'] = $value;
    }

    // Old: time_endorsed -> New: time
    public function getTimeEndorsedAttribute()
    {
        return $this->time;
    }

    public function setTimeEndorsedAttribute($value): void
    {
        $this->attributes['time'] = $value;
    }

    // Old: qty_delivered -> New: quantity_delivered
    public function getQtyDeliveredAttribute()
    {
        return $this->quantity_delivered;
    }

    public function setQtyDeliveredAttribute($value): void
    {
        $this->attributes['quantity_delivered'] = $value;
    }

    // Old: common_stretch_film_code -> New: stretch_film_code
    public function getCommonStretchFilmCodeAttribute()
    {
        return $this->stretch_film_code;
    }

    public function setCommonStretchFilmCodeAttribute($value): void
    {
        $this->attributes['stretch_film_code'] = $value;
    }

    // Old: csf_quantity was removed
    public function getCsfQuantityAttribute()
    {
        return null; // Deprecated field
    }

    // Old: customer -> New: customer_name
    public function getCustomerAttribute()
    {
        return $this->customer_name;
    }

    // Old: model -> New: model_name
    public function getModelAttribute()
    {
        return $this->model_name;
    }

    // New: etl_number alias for etl_code (for view compatibility)
    public function getEtlNumberAttribute()
    {
        return $this->etl_code;
    }

    // New: qty alias for quantity (for view compatibility)
    public function getQtyAttribute()
    {
        return $this->quantity;
    }

    public function setQtyAttribute($value): void
    {
        $this->attributes['quantity'] = $value;
    }

    // New: source_warehouse and destination_warehouse placeholders
    // These may be populated from related DeliverySchedule or JobOrder in the future
    public function getSourceWarehouseAttribute()
    {
        return $this->jobOrder?->product?->warehouse_location ?? '—';
    }

    public function getDestinationWarehouseAttribute()
    {
        return 'Destination';
    }

    // Legacy relationship accessor
    public function encodedBy(): BelongsTo
    {
        return $this->encodedByUser();
    }

    // ========== BOOT METHOD ==========
    
    protected static function booted(): void
    {
        static::creating(function (EndorseToLogistic $model) {
            // Auto-generate ETL code
            if (empty($model->etl_code)) {
                $model->etl_code = static::generateETLCode($model);
            }

            // Set time if not set
            if (empty($model->time)) {
                $model->time = now()->format('H:i:s');
            }

            // Set total_out from quantity if not set
            if (empty($model->total_out) && $model->quantity) {
                $model->total_out = $model->quantity;
            }

            // Set date if not set
            if (empty($model->date)) {
                $model->date = now();
            }
        });

        static::updating(function (EndorseToLogistic $model) {
            // Auto-update quantity_delivered when received
            if ($model->isDirty('date_received') && !$model->isDirty('quantity_delivered')) {
                $model->quantity_delivered = $model->quantity;
            }
        });
    }

    // ========== CODE GENERATION ==========
    
    public static function generateETLCode($model): string
    {
        // Use canonical ETL format: ETL-YYYY-####
        return static::nextEtlDeliveryCode();
    }

    public static function nextEtlDeliveryCode(): string
    {
        $year = now()->year;
        $last = static::where('etl_code', 'like', "ETL-{$year}-%")
            ->orderBy('etl_code', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->etl_code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        
        return sprintf('ETL-%d-%04d', $year, $nextNumber);
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getDeliveryStatusAttribute(): string
    {
        if ($this->isDelivered()) {
            return 'Delivered';
        }

        if ($this->delivery_date && $this->delivery_date < now()) {
            return 'Overdue';
        }

        return 'Pending';
    }

    public function getDaysUntilDeliveryAttribute(): ?int
    {
        if (!$this->delivery_date) {
            return null;
        }

        return now()->diffInDays($this->delivery_date, false);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->delivery_status) {
            'Delivered' => 'green',
            'Overdue' => 'red',
            'Pending' => 'yellow',
            default => 'gray',
        };
    }

    public function getHasDocumentsAttribute(): bool
    {
        return !empty($this->dr_number) && !empty($this->si_number);
    }

    public function getIsPendingAttribute(): bool
    {
        return !$this->isDelivered();
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->isOverdue()) {
            return null;
        }

        return now()->diffInDays($this->delivery_date);
    }
}