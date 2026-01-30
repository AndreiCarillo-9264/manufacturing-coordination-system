<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActualInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tag_number', 'product_id', 'fg_qty', 'uom', 'location',
        'counted_by_user_id', 'verified_by_user_id', 'remarks'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function countedBy()
    {
        return $this->belongsTo(User::class, 'counted_by_user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}