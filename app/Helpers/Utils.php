<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;

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

    public static function formatRangeHour($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return $start->format('H.i') . ' - ' . $end->format('H.i') . ' WIB';
    }

    public static function encode($string)
    {
        return base64_encode($string);
    }

    public static function decode($encrypted)
    {
        return base64_decode($encrypted);
    }

    public static function getDateFormat($dateString)
    {
        return Carbon::parse($dateString)
            ->locale('id')
            ->settings(['formatFunction' => 'translatedFormat'])
            ->translatedFormat('l, d F Y');
    }

    public static function getHourFormat($dateString)
    {
        return Carbon::parse($dateString)->format('H.i \W\I\B');
    }

    public static function isDateRange($startDate, $endDate)
    {
        $now = Carbon::now('Asia/Jakarta');
        $start = Carbon::parse($startDate)->startOfMinute();
        $end = Carbon::parse($endDate)->startOfMinute();

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }

    public static function isExpired($deadline)
    {
        return Carbon::now('Asia/Jakarta')->gt($deadline);
    }

    public static function generateRandomString()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->format('m');
        $date = $currentDate->format('d');
        $hour = $currentDate->format('H');

        $randomChars = Str::random(6);

        return $year . $month . $date . $hour . strtoupper($randomChars);
    }
}
