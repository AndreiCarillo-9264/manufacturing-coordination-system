<?php

namespace App\Events;

use App\Models\DeliverySchedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryScheduleCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deliverySchedule;

    public function __construct(DeliverySchedule $deliverySchedule)
    {
        $this->deliverySchedule = $deliverySchedule;
    }

    public function broadcastOn()
    {
        return [
            new Channel('delivery-schedules'),
            new PrivateChannel('delivery-schedule.' . $this->deliverySchedule->id),
            new PrivateChannel('job-order.' . $this->deliverySchedule->jo_id),
        ];
    }

    public function broadcastAs()
    {
        return 'delivery-schedule-created';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->deliverySchedule->id,
            'jo_id' => $this->deliverySchedule->jo_id,
            'jo_number' => $this->deliverySchedule->jobOrder?->jo_number,
            'delivered_dsd' => $this->deliverySchedule->delivered_dsd ?? 0,
            'message' => 'New Delivery Schedule created: ' . ($this->deliverySchedule->delivered_dsd ?? 0) . ' units',
            'created_at' => $this->deliverySchedule->created_at,
        ];
    }
}
