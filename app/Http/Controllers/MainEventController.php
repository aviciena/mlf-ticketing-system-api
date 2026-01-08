<?php

namespace App\Http\Controllers;

use App\Http\Resources\MainEventResource;
use App\Models\Events;
use Carbon\Carbon;

class MainEventController extends BaseController
{
    public function index()
    {
        // Rentang waktu Event Muncul (dalam 3 bulan sebelum dan sesudah event)

        $startRange = Carbon::now('Asia/Jakarta')->subMonths(3)->startOfMonth();
        $endRange = Carbon::now('Asia/Jakarta')->addMonths(3)->endOfMonth();

        $event = Events::with(['venue', 'status', 'subEvents', 'banners', 'eventTickets'])
            ->whereNull('parent_id')
            ->whereBetween('start_date', [$startRange, $endRange])
            ->orderBy('status_id', 'asc')
            ->orderBy('start_date', 'asc')->first();


        if ($event) {
            return $this->sendResponse(
                new MainEventResource($event),
                'Events retrieved successfully.'
            );
        }

        return $this->sendResponse(
            ['id' => null],
            'Events retrieved successfully.'
        );
    }
}
