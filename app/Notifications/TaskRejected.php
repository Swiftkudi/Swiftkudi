<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskRejected extends Notification
{
    use Queueable;

    protected $completion;

    public function __construct($completion)
    {
        $this->completion = $completion;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'task_rejected',
            'completion_id' => $this->completion->id,
            'task_id' => $this->completion->task_id,
            'reason' => $this->completion->admin_notes ?? null,
        ];
    }
}
