<?php

use App\Models\RoomParticipant;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    // Memeriksa apakah user adalah member dari room ini
    return RoomParticipant::where('room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists();
});
