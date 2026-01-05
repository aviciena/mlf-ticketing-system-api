<?php

namespace App\Models;

class EventsTicketCategory extends BaseModel
{
    public function eventTickets()
    {
        return $this->hasMany(EventTicket::class);
    }
}
