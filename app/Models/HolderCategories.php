<?php

namespace App\Models;

class HolderCategories extends BaseModel
{
    public function holders()
    {
        return $this->hasMany(Holder::class);
    }
}
