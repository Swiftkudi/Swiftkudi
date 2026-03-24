<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LevelUp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public int $newLevel;
    public int $oldLevel;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, int $newLevel, int $oldLevel)
    {
        $this->user = $user;
        $this->newLevel = $newLevel;
        $this->oldLevel = $oldLevel;
    }
}
