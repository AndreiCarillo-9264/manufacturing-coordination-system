<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActualInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tag_number',
        'product_id',
        'qty_counted',
        'location',
        'counted_by_user_id',
        'verified_by_user_id',
        'counted_at',
        'verified_at',
        'remarks',
    ];

    protected $casts = [
        'qty_counted' => 'integer',
        'counted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    // Helper methods
    public function markCounted(int $userId): void
    {
        $this->update([
            'counted_by_user_id' => $userId,
            'counted_at' => now(),
        ]);
    }

    public function markVerified(int $userId): void
    {
        $this->update([
            'verified_by_user_id' => $userId,
            'verified_at' => now(),
        ]);
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function isCounted(): bool
    {
        return !is_null($this->counted_at);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    // Sequence helper for tag_number
    public static function nextTagNumber(): string
    {
        $year = date('Y');
        $last = static::where('tag_number', 'like', "TAG-{$year}-%")->orderBy('tag_number', 'desc')->first();
        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->tag_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        return sprintf('TAG-%d-%04d', $year, $nextNumber);
    }

    // Ensure tag_number is set when creating
    protected static function booted(): void
    {
        static::creating(function (ActualInventory $inv) {
            if (empty($inv->tag_number)) {
                $inv->tag_number = static::nextTagNumber();
            }
        });
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    public function scopeCounted($query)
    {
        return $query->whereNotNull('counted_at');
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }
}