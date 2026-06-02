<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['reporter_id', 'reported_id', 'reason', 'evidence_snapshot', 'status'])]
class Reports extends Model
{

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'evidence_snapshot' => 'array', 
        ];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class);
    }

    public function reported()
    {
        return $this->belongsTo(User::class);
    }
}
