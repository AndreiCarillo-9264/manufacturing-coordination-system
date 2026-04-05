<?php

namespace App\Http\Controllers;

use App\Models\DeliverySchedule;
use App\Models\FinishedGood;
use App\Models\User;
use App\Notifications\SystemStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SystemStatusController extends Controller
{
    public function notify(Request $request)
    {
        $this->authorize('viewAny', DeliverySchedule::class);

        // Gather quick health checks
        $overdueDeliveries = DeliverySchedule::where('ds_status', '!=', 'DELIVERED')
            ->whereDate('delivery_date', '<', now()->toDateString())
            ->count();

        $lowStockCount = FinishedGood::whereColumn('current_qty', '<', 'buffer_stocks')->count();

        $issues = [];
        if ($overdueDeliveries > 0) {
            $issues[] = "{$overdueDeliveries} overdue delivery(ies)";
        }
        if ($lowStockCount > 0) {
            $issues[] = "{$lowStockCount} low-stock product(s)";
        }

        if (count($issues) === 0) {
            $message = 'System status: All systems operational. No reported downtime or breakdowns.';
            $status = 'ok';
        } else {
            $message = 'System alerts: ' . implode('; ', $issues) . '.';
            $status = 'issues';
        }

        $payload = [
            'type' => 'system_status',
            'status' => $status,
            'message' => $message,
            'details' => $issues,
            'url' => route('dashboard.index'),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Notify admins
        $admins = User::where('department', 'admin')->get();
        Notification::send($admins, new SystemStatusNotification($payload));

        return response()->json(['success' => true, 'message' => 'System status notified to admins.', 'payload' => $payload]);
    }
}
