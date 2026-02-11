<?php

namespace App\Observers;

use App\Models\ActivityLog;

class ModelObserver
{
    public function created($model)
    {
        $this->logActivity($model, 'created');
    }

    public function updated($model)
    {
        // Only log if actual changes were made
        if ($model->wasChanged()) {
            $this->logActivity($model, 'updated', $model->getOriginal());
        }
    }

    public function deleted($model)
    {
        $this->logActivity($model, 'deleted', $model->getAttributes());
    }

    protected function logActivity($model, $action, $oldValues = null)
    {
        // Skip logging for ActivityLog model itself
        if ($model instanceof ActivityLog) {
            return;
        }

        ActivityLog::create([
            'log_name' => class_basename(get_class($model)),
            'description' => $action,
            'subject_type' => get_class($model),
            'subject_id' => $model->id,
            'event' => $action,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'user_department' => auth()->user()?->department ?? null,
            'old_values' => $oldValues,
            'new_values' => $action !== 'deleted' ? $model->getAttributes() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'url' => request()->url(),
        ]);
    }
}
