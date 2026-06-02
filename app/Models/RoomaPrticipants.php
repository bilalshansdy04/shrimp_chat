<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['room_id', 'user_id', 'role', 'joined_at', 'cleared_at'])]
class RoomaPrticipants extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'cleared_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
