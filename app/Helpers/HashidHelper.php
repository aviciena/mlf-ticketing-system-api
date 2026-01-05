<?php

namespace App\Helpers;

use Vinkla\Hashids\Facades\Hashids;

class HashidHelper
{
    public static function encode($id)
    {
        return Hashids::encode($id);
    }

    public static function decode($hashid)
    {
        $decoded = Hashids::decode($hashid);
        return count($decoded) > 0 ? $decoded[0] : null;
    }
}
