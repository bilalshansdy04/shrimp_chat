<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'group_name', 'group_avatar'])]
class Room extends Model
{
    public function participants()
    {
        return $this->hasMany(RoomParticipant::class);
    }
}
