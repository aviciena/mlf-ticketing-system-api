<?php

namespace App\Models;

class EventsTicketCategory extends BaseModel
{
    protected $table = 'events_ticket_categories';

    public function eventTickets()
    {
        return $this->hasMany(EventTicket::class);
    }
}
