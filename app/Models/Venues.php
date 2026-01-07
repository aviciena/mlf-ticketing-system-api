<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venues extends BaseModel
{
    use HasFactory;

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
