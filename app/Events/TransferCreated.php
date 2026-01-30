<?php

namespace App\Events;

use App\Models\Transfer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transfer;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    public function broadcastOn()
    {
        return [
            new Channel('transfers'),
            new PrivateChannel('transfer.' . $this->transfer->id),
            new PrivateChannel('job-order.' . $this->transfer->jo_id),
        ];
    }

    public function broadcastAs()
    {
        return 'transfer-created';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->transfer->id,
            'jo_id' => $this->transfer->jo_id,
            'jo_number' => $this->transfer->jobOrder?->jo_number,
            'qty_received' => $this->transfer->qty_received ?? 0,
            'message' => 'New Transfer received: ' . ($this->transfer->qty_received ?? 0) . ' units',
            'created_at' => $this->transfer->created_at,
        ];
    }
}
