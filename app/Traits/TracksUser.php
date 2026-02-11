<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TracksUser
{
    protected static function bootTracksUser(): void
    {
        static::creating(function (Model $model) {
            // Set encoded_by if not already set and user is authenticated
            if (empty($model->encoded_by) && Auth::check()) {
                $model->encoded_by = Auth::id();
            }

            // Set date_encoded if field exists and not already set
            if (in_array('date_encoded', $model->getFillable()) && empty($model->date_encoded)) {
                $model->date_encoded = now();
            }
        });

        static::updating(function (Model $model) {
            // Set updated_by if user is authenticated
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function encodedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'encoded_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function scopeEncodedBy($query, $userId)
    {
        return $query->where('encoded_by', $userId);
    }

    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    public function scopeEncodedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_encoded', [$startDate, $endDate]);
    }

    public function scopeRecentlyEncoded($query, $days = 7)
    {
        return $query->where('date_encoded', '>=', now()->subDays($days));
    }

    public function wasEncodedBy($userId): bool
    {
        return $this->encoded_by == $userId;
    }

    public function wasUpdatedBy($userId): bool
    {
        return $this->updated_by == $userId;
    }

    public function wasEncodedByCurrentUser(): bool
    {
        return Auth::check() && $this->encoded_by == Auth::id();
    }

    public function wasUpdatedByCurrentUser(): bool
    {
        return Auth::check() && $this->updated_by == Auth::id();
    }

    public function getEncodedByNameAttribute(): ?string
    {
        return $this->encodedByUser?->name;
    }

    public function getUpdatedByNameAttribute(): ?string
    {
        return $this->updatedByUser?->name;
    }

    public function getEncodedAgoAttribute(): ?string
    {
        if (!$this->date_encoded) {
            return null;
        }

        return $this->date_encoded->diffForHumans();
    }

    public function setEncodedBy($userId): self
    {
        $this->encoded_by = $userId;
        return $this;
    }

    public function setUpdatedBy($userId): self
    {
        $this->updated_by = $userId;
        return $this;
    }
}