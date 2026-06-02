<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['blocker_id', 'blocked_id', 'type'])]
class Block extends Model
{
    const UPDATED_AT = null;
    
    public function blocker()
    {
        return $this->belongsTo(User::class);
    }

    public function blocked()
    {
        return $this->belongsTo(User::class);
    }
}
