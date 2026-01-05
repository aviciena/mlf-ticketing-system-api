<?php

namespace App\Models;

class Venues extends BaseModel
{
    protected $fillable = [
        'id',
        'title',
        'category',
        'description',
        'street',
        'city',
        'postal_code',
        'province',
        'maps_embed',
        'maps',
        'latitude',
        'longitude',
        'created_by',
        'updated_by'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function events()
    {
        return $this->hasMany(Events::class, "venue_id");
    }
}
