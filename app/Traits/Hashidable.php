<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

trait Hashidable
{
    public function getHashidAttribute()
    {
        return Hashids::encode($this->getKey());
    }

    public static function findByHashid($hashid)
    {
        $decoded = Hashids::decode($hashid);
        if (count($decoded) == 0) {
            return null;
        }
        return static::find($decoded[0]);
    }

    public static function decodeHashid($hashid)
    {
        $decoded = Hashids::decode($hashid);
        return $decoded[0] ?? null;
    }
}
