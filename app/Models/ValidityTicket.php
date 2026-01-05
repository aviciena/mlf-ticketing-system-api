<?php

namespace App\Models;

class ValidityTicket extends BaseModel
{
    public function eventTickets()
    {
        return $this->hasMany(EventTicket::class);
    }
}
