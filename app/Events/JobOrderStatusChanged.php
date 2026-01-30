<?php

namespace App\Events;

use App\Models\JobOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobOrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobOrder;
    public $oldStatus;
    public $newStatus;

    public function __construct(JobOrder $jobOrder, $oldStatus, $newStatus)
    {
        $this->jobOrder = $jobOrder;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function broadcastOn()
    {
        return [
            new Channel('job-orders'),
            new PrivateChannel('job-order.' . $this->jobOrder->id),
            new PrivateChannel('notifications.' . auth()->id()),
        ];
    }

    public function broadcastAs()
    {
        return 'job-order-status-changed';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->jobOrder->id,
            'jo_number' => $this->jobOrder->jo_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'product' => $this->jobOrder->product?->model_name,
            'message' => "Job Order {$this->jobOrder->jo_number} status changed from {$this->oldStatus} to {$this->newStatus}",
            'timestamp' => now(),
        ];
    }
}
