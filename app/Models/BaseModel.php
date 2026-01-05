<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('Asia/Jakarta')
            ->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('Asia/Jakarta')
            ->toDateTimeString();
    }
}
