<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['type', 'group_name', 'group_avatar'])]
class Room extends Model
{
    
}
