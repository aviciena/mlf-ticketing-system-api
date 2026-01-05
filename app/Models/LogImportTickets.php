<?php

namespace App\Models;

class LogImportTickets extends BaseModel
{
    protected $fillable = [
        'active_event_id',
        'ticket_id',
        'events_ticket_id',
        'events_ticket_title',
        'message'
    ];
}
