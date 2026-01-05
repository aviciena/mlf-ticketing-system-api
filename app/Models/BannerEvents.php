<?php

namespace App\Models;

class BannerEvents extends BaseModel
{
    protected $fillable = [
        "events_id",
        "file_name_id",
        "file_name",
        "path"
    ];

    public function event()
    {
        return $this->belongsTo(Events::class, 'events_id');
    }
}
