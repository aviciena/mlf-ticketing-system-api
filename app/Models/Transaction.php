<?php

namespace App\Models;

class Transaction extends BaseModel
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'event_id',
        'donation',
        'fee',
        'subtotal',
        'total_ticket',
        'total_price',
        'payment_type',
        'reference_code',
        'status'
    ];

    public function event()
    {
        return $this->belongsTo(Events::class);
    }

    public function eventTickets()
    {
        return $this->belongsToMany(EventTicket::class, 'transaction_details');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
