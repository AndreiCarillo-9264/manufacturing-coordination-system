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
        'user_id',
        'user_name',
        'user_department',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'properties',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'method',
        'url',
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for user() relationship (ActivityLog convention uses 'causer')
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    // Legacy relationship name
    public function model(): MorphTo
    {
        return $this->subject();
    }

    // ========== SCOPES ==========
    
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('event', $action);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByLogName($query, string $logName)
    {
        return $query->where('log_name', $logName);
    }

    public function scopeForSubject($query, string $subjectType, int $subjectId)
    {
        return $query->where('subject_type', $subjectType)
                     ->where('subject_id', $subjectId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('user_department', $department);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', strtoupper($method));
    }

    public function scopeCreated($query)
    {
        return $query->where('event', 'created');
    }

    public function scopeUpdated($query)
    {
        return $query->where('event', 'updated');
    }

    public function scopeDeleted($query)
    {
        return $query->where('event', 'deleted');
    }

    // ========== HELPER METHODS ==========
    
    public function getChanges(): array
    {
        if (empty($this->old_values) || empty($this->new_values)) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    public function wasFieldChanged(string $field): bool
    {
        $changes = $this->getChanges();
        return isset($changes[$field]);
    }

    public function getOldValue(string $field)
    {
        return $this->old_values[$field] ?? null;
    }

    public function getNewValue(string $field)
    {
        return $this->new_values[$field] ?? null;
    }

    public function getProperty(string $key)
    {
        return $this->properties[$key] ?? null;
    }

    // ========== COMPUTED ATTRIBUTES ==========
    
    public function getEventLabelAttribute(): string
    {
        return ucfirst($this->event);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getSubjectTypeNameAttribute(): string
    {
        if (!$this->subject_type) {
            return 'Unknown';
        }

        // Extract class name from full namespace
        $parts = explode('\\', $this->subject_type);
        return end($parts);
    }

    public function getChangesCountAttribute(): int
    {
        return count($this->getChanges());
    }

    public function getIsCreateEventAttribute(): bool
    {
        return $this->event === 'created';
    }

    public function getIsUpdateEventAttribute(): bool
    {
        return $this->event === 'updated';
    }

    public function getIsDeleteEventAttribute(): bool
    {
        return $this->event === 'deleted';
    }

    public function getUserDisplayNameAttribute(): string
    {
        return $this->user_name ?? 'System';
    }

    public function getFormattedChangesAttribute(): string
    {
        $changes = $this->getChanges();
        if (empty($changes)) {
            return 'No changes';
        }

        $formatted = [];
        foreach ($changes as $field => $change) {
            $formatted[] = ucfirst($field) . ': ' . 
                          json_encode($change['old']) . ' → ' . 
                          json_encode($change['new']);
        }

        return implode(', ', $formatted);
    }

    // ========== STATIC HELPER METHODS ==========
    
    public static function logActivity(
        string $description,
        Model $subject = null,
        array $properties = [],
        string $logName = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'user_department' => auth()->user()?->department,
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'url' => request()->fullUrl(),
        ]);
    }

    public static function getRecentActivity(int $limit = 50)
    {
        return static::with(['user', 'subject'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public static function getActivityForUser(int $userId, int $limit = 50)
    {
        return static::byUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public static function getActivityForModel(Model $model)
    {
        return static::forSubject(get_class($model), $model->id)
            ->latest()
            ->get();
    }
}