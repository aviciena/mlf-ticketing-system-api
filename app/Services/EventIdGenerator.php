<?php

namespace App\Services;

use App\Models\Events;
use Illuminate\Support\Str;

class EventIdGenerator
{
    public static function generate(): string
    {
        // Adding 8 random character
        $randomChars = Str::random(8);
        return strtoupper($randomChars);
    }

    public static function generateUnique(): string
    {
        do {
            $id = self::generate();
        } while (Events::where('id', $id)->exists());

        return $id;
    }
}
