<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['room_id', 'inviter_id', 'invitee_id', 'status'])]
class GroupInvitations extends Model
{
    const UPDATED_AT = null;
    
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class);
    }

    public function invitee()
    {
        return $this->belongsTo(User::class);
    }
}
