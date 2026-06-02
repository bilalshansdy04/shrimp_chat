<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'full_name', 'birt_date', 'gender', 'avatar_url'])]
class Profile extends Model
{
    protected function casts(): array
    {
        return [
            'birt_date' => 'date',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
