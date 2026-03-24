<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class RevenueUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $payload;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('admin.revenue');
    }

    public function broadcastAs()
    {
        return 'RevenueUpdated';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}
