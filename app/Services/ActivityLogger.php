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
            'action' => $action,
            'user_id' => $user?->id,
            'ip_address' => $extra['ip'] ?? Request::ip(),
            'user_agent' => $extra['user_agent'] ?? Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ];

        if ($model) {
            $data['model_type'] = get_class($model);
            $data['model_id'] = $model->getKey();
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