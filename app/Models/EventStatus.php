<?php

namespace App\Models;

class EventStatus extends BaseModel
{
    protected $table = 'event_status';

    public function events()
    {
        return $this->hasMany(Events::class);
    }
}
