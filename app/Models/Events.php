<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Events extends BaseModel
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'venue_id',
        'status_id',
        'title',
        'start_date',
        'end_date',
        'icon',
        'auto_sync',
        'is_sync_interval',
        'sync_query',
        'event_external_id',
        'endpoint',
        'api_key',
        'created_by',
        'updated_by',
        'description',
        'parent_id'
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venues::class);
    }

    public function status()
    {
        return $this->belongsTo(EventStatus::class);
    }

    public function tickets()
    {
        return $this->hasMany(EventTicket::class);
    }

    // Untuk mengambil sub-events dari event ini
    public function subEvents()
    {
        return $this->hasMany(Events::class, 'parent_id');
    }

    // Untuk mengetahui induk dari sub-event ini
    public function parentEvent()
    {
        return $this->belongsTo(Events::class, 'parent_id');
    }

    public function banners()
    {
        return $this->hasMany(BannerEvents::class, 'events_id');
    }

    public function eventTickets()
    {
        return $this->hasMany(EventTicket::class, 'event_id');
    }

    public function gates(): BelongsToMany
    {
        return $this->belongsToMany(
            Gate::class,      // Model tujuan
            'gate_events',    // Nama tabel pivot
            'event_id',       // Foreign key di tabel pivot untuk model ini
            'gate_id'         // Foreign key di tabel pivot untuk model tujuan
        );
    }
}
