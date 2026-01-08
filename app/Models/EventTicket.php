<?php

namespace App\Models;

class EventTicket extends BaseModel
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'event_id',
        'title',
        'event_ticket_category_id',
        'start_date',
        'end_date',
        'sale_start_date',
        'sale_end_date',
        'min_quantity',
        'max_quantity',
        'quota',
        'price',
        'original_price',
        'discount_type',
        'discount_amount',
        'price_after_discount',
        'allow_multiple_checkin',
        'validity_type_id',
        'auto_checkout',
        'external_event_ticket_id',
        'created_by',
        'updated_by',
        'description'
    ];

    public function category()
    {
        return $this->belongsTo(EventsTicketCategory::class, "event_ticket_category_id", "id");
    }

    public function validityType()
    {
        return $this->belongsTo(ValidityTicket::class);
    }

    public function event()
    {
        return $this->belongsTo(Events::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'events_ticket_id');
    }

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'transaction_details');
    }
}
