<?php

namespace App\Models;

class Ticket extends BaseModel
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'parent_id',
        'transaction_id',
        'events_ticket_id',
        'ticket_status_id',
        'payment_status_id',
        'holder_ticket_id',
        'validity_ticket_id',
        'validity_start_date',
        'validity_end_date',
        'allow_multiple_checkin',
        'gates_id',
        'created_by',
        'updated_by',
    ];

    public function gateTickets()
    {
        return $this->hasMany(GateTicket::class);
    }

    public function eventTicket()
    {
        return $this->belongsTo(EventTicket::class, 'events_ticket_id');
    }

    public function ticketStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
    }

    public function holder()
    {
        return $this->belongsTo(Holder::class, 'holder_ticket_id');
    }

    public function validityTicket()
    {
        return $this->belongsTo(ValidityTicket::class, 'validity_ticket_id');
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class, 'gates_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Untuk mengambil sub-events dari event ini
    public function subTickets()
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }

    // Untuk mengetahui induk dari sub-event ini
    public function parentTicket()
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }
}
