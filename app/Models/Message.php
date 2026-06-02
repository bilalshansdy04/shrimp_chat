<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['room_id', 'sender_id', 'content', 'reply_to_id', 'forwarded_msg_id', 'media_url', 'thumbnail_url', 'media_type', 'is_deleted'])]
class Message extends Model
{
    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }
    
    public function forwardedMsg()
    {
        return $this->belongsTo(Message::class, 'forwarded_msg_id');
    }
}
