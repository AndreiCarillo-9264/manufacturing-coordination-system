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
        // Build a more descriptive action summary for logs
        $summary = $this->buildLogSummary();

        // For updates, include a concise change summary
        $changeParts = null;
        if ($event === 'updated') {
            $original = $this->getOriginal();
            $attributes = $this->getAttributes();
            $changes = [];
            foreach ($attributes as $key => $value) {
                $old = $original[$key] ?? null;
                if ($old !== $value) {
                    $changes[] = $key . ': ' . (is_scalar($old) ? $old : json_encode($old)) . ' → ' . (is_scalar($value) ? $value : json_encode($value));
                }
            }
            if (!empty($changes)) {
                // limit to first 6 changes to avoid huge descriptions
                $changeParts = implode(', ', array_slice($changes, 0, 6));
            }
        }

        $description = ucfirst($event) . ' ' . class_basename(get_class($this));
        if ($this->id) {
            $description .= ' #' . $this->id;
        }
        if ($summary) {
            $description .= ' (' . $summary . ')';
        }
        if ($changeParts) {
            $description .= ' - Changes: ' . $changeParts;
        }

        ActivityLog::create([
            'log_name' => class_basename(get_class($this)),
            'description' => $description,
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

    protected function buildLogSummary(): ?string
    {
        // Prefer common identifier attributes across models
        $candidates = [
            'jo_number' => 'JO',
            'ds_code' => 'DS',
            'etl_code' => 'ETL',
            'transfer_code' => 'Transfer',
            'ptt_number' => 'PTT',
            'fg_code' => 'FG',
            'product_code' => 'Product',
            'tag_number' => 'Tag',
            'name' => 'Name',
            'model_name' => 'Model',
            'product_id' => 'ProductID',
        ];

        $parts = [];
        foreach ($candidates as $field => $label) {
            if (isset($this->{$field}) && $this->{$field} !== null && $this->{$field} !== '') {
                $parts[] = $label . ': ' . $this->{$field};
            }
        }

        if (empty($parts)) return null;
        return implode(', ', array_slice($parts, 0, 4));
    }
}