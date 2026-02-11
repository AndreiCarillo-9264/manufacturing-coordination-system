<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }

    // Legacy relationship name
    public function chatHistories(): HasMany
    {
        return $this->messages();
    }

    // ========== SCOPES ==========
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== HELPER METHODS ==========
    
    public function activate(): self
    {
        $this->is_active = true;
        $this->save();
        return $this;
    }

    public function deactivate(): self
    {
        $this->is_active = false;
        $this->save();
        return $this;
    }

    public function addMessage(string $userMessage, string $aiResponse): ChatHistory
    {
        return $this->messages()->create([
            'user_id' => $this->user_id,
            'user_message' => $userMessage,
            'ai_response' => $aiResponse,
        ]);
    }

    public function updateTitle(string $title): self
    {
        $this->title = $title;
        $this->save();
        return $this;
    }

    public function getLastMessage(): ?ChatHistory
    {
        return $this->messages()->latest()->first();
    }

    public function clearMessages(): self
    {
        $this->messages()->delete();
        return $this;
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getMessageCountAttribute(): int
    {
        return $this->messages()->count();
    }

    public function getLastActivityAttribute(): ?string
    {
        return $this->updated_at?->diffForHumans();
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->message_count === 0;
    }
}