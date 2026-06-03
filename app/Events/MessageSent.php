<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(    $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // new PrivateChannel('room.' . $this->message->room_id),
            new Channel('room.' . $this->message->room_id),
        ];
    }

    /**
     * Nama event yang akan ditangkap oleh frontend
     */
    public function broadcastAs(): string
    {
        return 'message.new';
    }

    /**
     * Data apa yang dibawa oleh kurir
     */
    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender.profile');

        return [
            'message' => $this->message->toArray(),
        ];
    }
}
