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
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => $action,
            'user_id' => auth()->id(),
            'old_values' => $oldValues,
            'new_values' => $action !== 'deleted' ? $model->getAttributes() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
