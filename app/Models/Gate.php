<?php

namespace App\Models;

use App\Traits\Hashidable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Gate extends BaseModel
{
    use Hashidable;

    protected $fillable = [
        'code',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function checkInGateTickets()
    {
        return $this->hasMany(GateTicket::class, 'checkin_gate_id');
    }

    public function checkOutGateTickets()
    {
        return $this->hasMany(GateTicket::class, 'checkout_gate_id');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            Events::class,     // Model tujuan
            'gate_events',    // Nama tabel pivot
            'gate_id',        // Foreign key di tabel pivot untuk model ini
            'event_id'        // Foreign key di tabel pivot untuk model tujuan
        );
    }
}
