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

    // Relasi ke tabel Transactions
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    // Relasi ke tabel EventTickets
    public function eventTicket()
    {
        return $this->belongsTo(EventTicket::class, 'event_ticket_id', 'id');
    }
}
