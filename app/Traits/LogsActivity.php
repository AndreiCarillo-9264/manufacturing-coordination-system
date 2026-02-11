<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        foreach (static::getModelEvents() as $event) {
            static::$event(function ($model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    protected static function getModelEvents()
    {
        return ['created', 'updated', 'deleted'];
    }

    protected function logActivity($event)
    {
        ActivityLog::create([
            'log_name' => class_basename(get_class($this)),
            'description' => $event,
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'event' => $event,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'user_department' => auth()->user()?->department ?? null,
            'old_values' => $event === 'updated' ? $this->getOriginal() : null,
            'new_values' => $event !== 'deleted' ? $this->getAttributes() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'url' => request()->url(),
        ]);
    }
}