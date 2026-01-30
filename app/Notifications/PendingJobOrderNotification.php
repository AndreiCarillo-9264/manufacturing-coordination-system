<?php

namespace App\Notifications;

use App\Models\JobOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PendingJobOrderNotification extends Notification
{
    use Queueable;

    protected $jobOrder;

    public function __construct(JobOrder $jobOrder)
    {
        $this->jobOrder = $jobOrder;
    }

    public function via(object $notifiable)
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable)
    {
        return [
            'type' => 'pending_job_order',
            'job_order_id' => $this->jobOrder->id,
            'jo_number' => $this->jobOrder->jo_number,
            'product_name' => $this->jobOrder->product?->model_name ?? $this->jobOrder->product?->product_code,
            'quantity' => $this->jobOrder->qty,
            'uom' => $this->jobOrder->uom,
            'date_needed' => $this->jobOrder->date_needed?->format('Y-m-d'),
            'message' => "New pending Job Order: {$this->jobOrder->jo_number} - {$this->jobOrder->product?->model_name} ({$this->jobOrder->qty} {$this->jobOrder->uom})",
            'url' => route('job-orders.show', $this->jobOrder->id),
        ];
    }
}
