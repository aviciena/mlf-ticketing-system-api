<?php

namespace App\Models;

class Roles extends BaseModel
{
    protected $guarded = [];

    protected $fillable = [
        'code',
        'description'
    ];
}
