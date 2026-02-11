<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public function logSystem(
        string $action,
        array $extra = [],
        ?User $user = null
    ): ActivityLog {
        return $this->createLog(
            action: $action,
            user: $user,
            extra: $extra
        );
    }

    public function logModel(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): ActivityLog {
        return $this->createLog(
            action: $action,
            user: $user,
            model: $model,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }

    protected function createLog(
        string $action,
        ?User $user = null,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $extra = []
    ): ActivityLog {
        $user ??= Auth::user();

        $data = [
            'event' => $action,
            'description' => $action,
            'log_name' => $model ? class_basename(get_class($model)) : 'system',
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_department' => $user?->department ?? null,
            'ip_address' => $extra['ip'] ?? Request::ip(),
            'user_agent' => $extra['user_agent'] ?? Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'method' => Request::method(),
            'url' => Request::url(),
        ];

        if ($model) {
            $data['subject_type'] = get_class($model);
            $data['subject_id'] = $model->getKey();
        }

        return ActivityLog::create($data);
    }

    public function logLogin(?User $user = null): ActivityLog
    {
        return $this->logSystem('User logged in', [], $user);
    }

    public function logLogout(?User $user = null): ActivityLog
    {
        return $this->logSystem('User logged out', [], $user);
    }

    public function logFailedLogin(string $identifier): ActivityLog
    {
        return $this->logSystem('Failed login attempt', [
            'identifier' => $identifier
        ]);
    }
}