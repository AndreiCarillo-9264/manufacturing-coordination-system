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
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action' => $event,
            'user_id' => auth()->id(),
            'old_values' => $event === 'updated' ? $this->getOriginal() : null,
            'new_values' => $event !== 'deleted' ? $this->getAttributes() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}