<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GateEvent extends Model
{
    protected $fillable = [
        'gate_id',
        'event_id'
    ];
}
