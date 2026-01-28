<?php

namespace App\Services;

use App\Models\Venues;

class VenueIdGenerator
{
    public static function generate(string $title): string
    {
        // Get first letter from each sentence
        $words = preg_split("/\s+/", trim($title));
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtolower(substr($word, 0, 1));
        }

        // Adding 4 random digits
        $randomDigits = rand(1000, 9999);

        return $initials . $randomDigits;
    }

    public static function generateUnique(string $title): string
    {
        do {
            $id = self::generate($title);
        } while (Venues::where('id', $id)->exists());

        return $id;
    }
}
