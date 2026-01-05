<?php

namespace App\Services;

use App\Models\EventTicket;

class EventTicketIdGenerator
{
    public static function generate(): string
    {
        // Generate 6 random digits
        $randomDigits = rand(100000, 999999);

        return $randomDigits;
    }

    public static function generateUnique($eventId): string
    {
        do {
            $id = self::generate();
        } while (EventTicket::where('id', $id)->where('event_id', $eventId)->exists());

        return $id;
    }
}
