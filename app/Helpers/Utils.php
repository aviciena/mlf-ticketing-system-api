<?php

namespace App\Helpers;

use Carbon\Carbon;

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

    public static function formatRange($dateStart, $dateEnd)
    {
        $start = Carbon::parse($dateStart)->locale('id'); // Set bahasa Indonesia
        $end = Carbon::parse($dateEnd)->locale('id');

        // Aktifkan translasi bulan ke Indonesia
        $start->settings(['formatFunction' => 'translatedFormat']);
        $end->settings(['formatFunction' => 'translatedFormat']);

        if ($start->year === $end->year) {
            // Jika tahun sama: 05 Desember - 07 Desember 2025
            return $start->translatedFormat('d F') . ' - ' . $end->translatedFormat('d F Y');
        } else {
            // Jika tahun berbeda: 05 Desember 2025 - 01 Januari 2026
            return $start->translatedFormat('d F Y') . ' - ' . $end->translatedFormat('d F Y');
        }
    }
}
