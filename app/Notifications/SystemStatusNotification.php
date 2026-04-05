<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemStatusNotification extends Notification
{
    use Queueable;

    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via(object $notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable)
    {
        return $this->payload;
    }

    public function toDatabase(object $notifiable)
    {
        return $this->payload;
    }
}
