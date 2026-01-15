<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Http\Resources\MainEventResource;
use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainEventController extends BaseController
{
    public function index()
    {
        return $this->mainResult(null);
    }

    public function find($id)
    {
        $base64 = str_replace(['-', '_'], ['+', '/'], $id);
        return $this->mainResult(Utils::decode($base64));
    }

    private function mainResult($id)
    {
        $now = Carbon::now('Asia/Jakarta');
        // Rentang waktu Event Muncul (dalam 3 bulan sebelum dan sesudah event)
        $startRange = Carbon::now('Asia/Jakarta')->subMonths(3)->startOfMonth();
        $endRange = Carbon::now('Asia/Jakarta')->addMonths(3)->endOfMonth();

        if ($id) {
            $query = Events::with(['venue', 'status', 'subEvents', 'banners', 'eventTickets' => function ($query) use ($now) {
                $query->orderBy('start_date', 'asc')
                    ->orderBy('title', 'asc');
            }])->where('id', $id);
        } else {
            $query = Events::with(['venue', 'status', 'subEvents', 'banners', 'eventTickets' => function ($query) use ($now) {
                $query->whereNotNull('price')
                    ->where('sale_start_date', '<=', $now)
                    ->where('sale_end_date', '>=', $now)
                    ->orderBy('start_date', 'asc')
                    ->orderBy('title', 'asc');
            }])->whereNull('parent_id');
        }

        $query->whereBetween('start_date', [$startRange, $endRange])
            ->orderBy('status_id', 'asc')
            ->orderBy('start_date', 'asc');

        $event = $query->first();

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
