<?php

namespace App\Http\Controllers;

use App\Models\FinishedGood;
use App\Models\DeliverySchedule;
use App\Models\JobOrder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = auth()->user();
        $notifications = [];

        // Database notifications from the notification table
        $unreadDatabaseNotifications = $user->unreadNotifications()->get();

        foreach ($unreadDatabaseNotifications as $dbNotif) {
            $data = $dbNotif->data;
            $notifications[] = [
                'id' => $dbNotif->id,
                'type' => $this->mapNotificationType($data['type'] ?? ''),
                'icon' => $this->getNotificationIcon($data['type'] ?? ''),
                'title' => $this->getNotificationTitle($data['type'] ?? ''),
                'message' => $data['message'] ?? '',
                'time' => $dbNotif->created_at->diffForHumans(),
                'link' => $data['url'] ?? '#',
                'database_id' => $dbNotif->id,
            ];
        }

        // Low stock alerts
        $lowStockCount = FinishedGood::lowStock()->count();
        if ($lowStockCount > 0 && in_array($user->department, ['production', 'inventory', 'admin'])) {
            $notifications[] = [
                'id' => 'low-stock',
                'type' => 'warning',
                'icon' => 'fa-exclamation-triangle',
                'title' => 'Low Stock Alert',
                'message' => "{$lowStockCount} product(s) are below buffer stock level",
                'time' => 'Just now',
                'link' => route('finished-goods.index', ['low_stock' => 1])
            ];
        }

        // Delayed deliveries
        $delayedCount = DeliverySchedule::delayed()->count();
        if ($delayedCount > 0 && in_array($user->department, ['logistics', 'sales', 'admin'])) {
            $notifications[] = [
                'id' => 'delayed-delivery',
                'type' => 'danger',
                'icon' => 'fa-clock',
                'title' => 'Delayed Deliveries',
                'message' => "{$delayedCount} delivery schedule(s) are past due",
                'time' => 'Just now',
                'link' => route('delivery-schedules.index', ['delayed' => 1])
            ];
        }

        // Pending approvals - for inventory department
        if ($user->department === 'inventory') {
            $pendingCount = JobOrder::pending()->count();
            if ($pendingCount > 0) {
                $notifications[] = [
                    'id' => 'pending-approval',
                    'type' => 'info',
                    'icon' => 'fa-clipboard-list',
                    'title' => 'Pending Approvals',
                    'message' => "{$pendingCount} job order(s) awaiting approval",
                    'time' => 'Just now',
                    'link' => route('job-orders.index', ['status' => 'pending'])
                ];
            }
        }

        // Production jobs not started - for production department
        if ($user->department === 'production') {
            $notStartedCount = JobOrder::approved()->count();
            if ($notStartedCount > 0) {
                $notifications[] = [
                    'id' => 'production-pending',
                    'type' => 'info',
                    'icon' => 'fa-hammer',
                    'title' => 'Production Pending',
                    'message' => "{$notStartedCount} job order(s) approved and ready to produce",
                    'time' => 'Just now',
                    'link' => route('dashboard.production')
                ];
            }
        }

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => count($notifications),
        ]);
    }

    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('id');
        $user = auth()->user();

        $notification = $user->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    private function mapNotificationType(string $type): string
    {
        return match ($type) {
            'pending_job_order' => 'info',
            'job_order_approved' => 'success',
            'job_order_completed' => 'success',
            'job_order_cancelled' => 'danger',
            'low_stock' => 'warning',
            'delayed_delivery' => 'danger',
            default => 'info',
        };
    }

    private function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'pending_job_order' => 'fa-clipboard-list',
            'job_order_approved' => 'fa-check-circle',
            'job_order_completed' => 'fa-check-double',
            'job_order_cancelled' => 'fa-times-circle',
            'low_stock' => 'fa-exclamation-triangle',
            'delayed_delivery' => 'fa-clock',
            default => 'fa-bell',
        };
    }

    private function getNotificationTitle(string $type): string
    {
        return match ($type) {
            'pending_job_order' => 'New Job Order',
            'job_order_approved' => 'Job Order Approved',
            'job_order_completed' => 'Job Order Completed',
            'job_order_cancelled' => 'Job Order Cancelled',
            'low_stock' => 'Low Stock Alert',
            'delayed_delivery' => 'Delayed Delivery',
            default => 'Notification',
        };
    }
}