<?php

namespace App\Events;

use App\Models\JobOrder;
use App\Models\User;
use App\Notifications\PendingJobOrderNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobOrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobOrder;

    public function __construct(JobOrder $jobOrder)
    {
        $this->jobOrder = $jobOrder;

        // Notify inventory department users if job order is pending
        if ($jobOrder->status === 'pending') {
            $inventoryUsers = User::where('department', 'inventory')->get();
            foreach ($inventoryUsers as $user) {
                $user->notify(new PendingJobOrderNotification($jobOrder));
            }
        }
    }

    public function broadcastOn()
    {
        return [
            new Channel('job-orders'),
            new PrivateChannel('job-order.' . $this->jobOrder->id),
        ];
    }

    public function broadcastAs()
    {
        return 'job-order-created';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->jobOrder->id,
            'jo_number' => $this->jobOrder->jo_number,
            'status' => $this->jobOrder->status,
            'product' => $this->jobOrder->product?->model_name ?? $this->jobOrder->product?->product_code,
            'quantity' => $this->jobOrder->qty,
            'user' => auth()->user()?->name ?? 'System',
            'message' => 'New Job Order created: ' . $this->jobOrder->jo_number,
            'created_at' => $this->jobOrder->created_at,
        ];
    }
}