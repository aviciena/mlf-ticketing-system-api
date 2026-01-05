<?php

namespace App\Models;

use App\Traits\Hashidable;

class Holder extends BaseModel
{
    use Hashidable;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function holderCategory()
    {
        return $this->belongsTo(HolderCategories::class, 'category_id');
    }
}
