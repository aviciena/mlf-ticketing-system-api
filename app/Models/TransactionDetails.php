<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    protected $fillable = [
        'transaction_id',
        'event_ticket_id',
        'qty',
        'price',
        'subtotal'
    ];
}
