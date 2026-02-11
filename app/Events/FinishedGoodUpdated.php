<?php

namespace App\Events;

use App\Models\FinishedGood;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FinishedGoodUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $finishedGood;

    public function __construct(FinishedGood $finishedGood)
    {
        $this->finishedGood = $finishedGood;
    }

    public function broadcastOn()
    {
        return [
            new Channel('finished-goods'),
            new PrivateChannel('finished-good.' . $this->finishedGood->id),
        ];
    }

    public function broadcastAs()
    {
        return 'finished-good-updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->finishedGood->id,
            'product' => $this->finishedGood->product?->model_name ?? $this->finishedGood->product?->product_code,
            'qty_in' => $this->finishedGood->qty_in,
            'amount_in' => $this->finishedGood->amount_in,
            'message' => 'Finished goods inventory updated',
            'timestamp' => now(),
        ];
    }
}
