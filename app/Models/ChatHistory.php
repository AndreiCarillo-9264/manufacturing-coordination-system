<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    protected $fillable = [
        'user_id',
        'conversation_id',
        'user_message',
        'ai_response',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    // ========== SCOPES ==========
    
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('user_message', 'LIKE', "%{$search}%")
              ->orWhere('ai_response', 'LIKE', "%{$search}%");
        });
    }

    // ========== HELPER METHODS ==========
    
    public function updateMessage(string $userMessage = null, string $aiResponse = null): self
    {
        if ($userMessage !== null) {
            $this->user_message = $userMessage;
        }
        
        if ($aiResponse !== null) {
            $this->ai_response = $aiResponse;
        }
        
        $this->save();
        return $this;
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getUserMessagePreviewAttribute(): string
    {
        return \Str::limit($this->user_message, 50);
    }

    public function getAiResponsePreviewAttribute(): string
    {
        return \Str::limit($this->ai_response, 50);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getUserMessageLengthAttribute(): int
    {
        return strlen($this->user_message);
    }

    public function getAiResponseLengthAttribute(): int
    {
        return strlen($this->ai_response);
    }
}