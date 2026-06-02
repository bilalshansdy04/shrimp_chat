<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'action', 'payload'])]
class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'payload' => 'array', 
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
