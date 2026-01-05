<?php

namespace App\Helpers;

class Utils
{
    public static function generateRandomId(string $title): string
    {
        // Get first letter from each sentence
        $words = preg_split("/\s+/", trim($title));
        $prefix = '';

        foreach ($words as $word) {
            $prefix .= strtolower(substr($word, 0, 1));
        }

        $prefix = count($words) < 3 && strlen($title) < 7 ? strtolower(str_replace(" ", "", $title)) : $prefix;

        // Adding 4 random digits
        $randomNumber = mt_rand(1000, 9999);

        return $prefix . $randomNumber;
    }

    public static function randomHexColor()
    {
        return sprintf("#%06X", mt_rand(0, 0xFFFFFF));
    }
}
