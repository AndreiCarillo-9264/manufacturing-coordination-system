<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'user_id',          // causer
        'model_type',       // subject type
        'model_id',         // subject id
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        // you can add more: e.g. 'extra_properties' => 'array'
    ];

    protected $casts = [
        'old_values'   => 'array',
        'new_values'   => 'array',
        // 'extra_properties' => 'array',  // ← optional future extension
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }
}