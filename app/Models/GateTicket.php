<?php

namespace App\Models;

use App\Traits\Hashidable;

class GateTicket extends BaseModel
{
    use Hashidable;

    protected $fillable = [
        'ticket_id',
        'checkin_gate_id',
        'checkout_gate_id',
        'check_in_date',
        'check_out_date',
        'updated_date',
        'remarks'
    ];

    public function gate()
    {
        return $this->belongsTo(Gate::class, 'checkin_gate_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function checkoutGate()
    {
        return $this->belongsTo(Gate::class, 'checkout_gate_id');
    }
}
