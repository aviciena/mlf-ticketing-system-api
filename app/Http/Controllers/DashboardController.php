<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\Events;
use App\Models\Ticket;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        $checkInOutSeries = [];
        $user = $request->user();
        $event = Events::with('status')->selectRaw('id, title, status_id, start_date, end_date')->where('id', $user->event_id)->first();

        $startDate = $event->start_date;
        $endDate = $event->end_date;
        $isEventActive = $event->status->code == "active";
        $eventDate = '(' . Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y') . ')';

        // Ticket Check-In Check-Out
        $checkInResp = null;
        $checkOutResp = null;

        if ($isEventActive) {
            $checkInResp = $this->getCheckInOutResults('check_in_date', $event->id);
            $checkOutResp = $this->getCheckInOutResults('check_out_date', $event->id);
        }

        // Total Ticket Generated
        $eventTickets = Ticket::join('event_tickets as et', 'et.id', '=', 'tickets.events_ticket_id')
            ->select('et.title as name', DB::raw('COUNT(et.title) as count'))
            ->leftJoin('events as e', 'e.id', '=', 'et.event_id')
            ->where('e.id', $event->id)
            ->groupBy('et.id', 'et.title')
            ->orderBy('et.title')
            ->get();

        $colors = ['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0', '#008ffb', '#00e396'];
        $totalTicketResp = [];

        for ($i = 0; $i < count($eventTickets); $i++) {
            $totalTicketResp[] = [
                'name' => $eventTickets[$i]->name,
                'data' => [$eventTickets[$i]->count],
                'color' => $i < count($colors) ? $colors[$i] : Utils::randomHexColor()
            ];

            $checkInOutSeries[$eventTickets[$i]->name] = [
                'name' => $eventTickets[$i]->name,
                'data' => [],
                'color' => $i < count($colors) ? $colors[$i] : Utils::randomHexColor()
            ];
        }

        // Totak Ticket Check-In Check-Out
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        $i = 1;
        $axis = [];
        $totalByDay = [];
        foreach ($period as $date) {
            // Create xAxis value
            $fDate = Carbon::parse($date)->format("d/m/Y");
            $axis[] = "Day $i ($fDate)";
            $i += 1;

            foreach ($checkInOutSeries as $key => &$series) {
                $series['data'][] = 0;
            }
            unset($series); // break reference

            $fDate = Carbon::parse($date)->format("Y-m-d");
            $checkInResults = $this->getCheckInOutResults('check_in_date', $event->id, $fDate);
            $total = 0;

            $checkInResults->each(function ($result) use (&$checkInOutSeries, &$total) {
                $title = $result->title;

                // Gunakan helper data_get agar lebih aman
                $data = &$checkInOutSeries[$title]['data'];
                $lastIndex = array_key_last($data);

                if ($lastIndex !== null) {
                    $data[$lastIndex] += $result->count;
                    $total += $result->count;
                }
            });

            $checkOutResults = $this->getCheckInOutResults('check_out_date', $event->id, $fDate);

            $checkOutResults->each(function ($result) use (&$checkOutResultsSeries, &$total) {
                $title = $result->title;

                // Gunakan helper data_get agar lebih aman
                $data = &$checkOutResultsSeries[$title]['data'];
                $lastIndex = array_key_last($data);

                if ($lastIndex !== null) {
                    $data[$lastIndex] += $result->count;
                    $total += $result->count;
                }
            });

            $totalByDay[] = $total;
        }

        $series = [];
        foreach ($checkInOutSeries as $data) {
            $series[] = $data;
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard retrieved successfully.',
            'data' => [
                'event' => [
                    'title' => $event->title,
                    'date' => $eventDate
                ],
                'charts' => [
                    'total_tickets_created' => $totalTicketResp,
                    'check_in' => $checkInResp,
                    'check_out' => $checkOutResp,
                    'total_checkin_checkout' => [
                        'xaxis' => $axis,
                        'series' => $series
                    ],
                    'total_checkin_checkout_by_day' => [
                        'xaxis' => $axis,
                        'series' => $totalByDay
                    ]
                ]
            ]
        ]);
    }

    private function getCheckInOutResults($type, $eventId, $date = null)
    {
        $gateType = 'gtt.check_out_date';

        $subQuery = DB::table('gate_tickets')
            ->select(DB::raw('MAX(id) as max_id'), 'ticket_id')
            ->whereDate($type, $date ?? Carbon::today());

        if ($type == "check_in_date") {
            $subQuery->whereNull('check_out_date');
            $gateType = 'gtt.check_in_date';
        } else {
            $checkInQuery = DB::table('gate_tickets')
                ->select('ticket_id')
                ->whereDate('check_in_date', $date ?? Carbon::today())
                ->whereNull('check_out_date')
                ->groupBy('ticket_id');

            $subQuery->whereNotNull('check_out_date');
            $subQuery->whereNotIn('ticket_id', $checkInQuery);
        }

        $subQuery->groupBy('ticket_id');

        $result = DB::table(DB::raw("({$subQuery->toSql()}) as gt"))
            ->mergeBindings($subQuery)
            ->leftJoin('gate_tickets as gtt', 'gtt.id', '=', 'gt.max_id')
            ->leftJoin('tickets as tk', 'tk.id', '=', 'gtt.ticket_id')
            ->leftJoin('event_tickets as et', 'et.id', '=', 'tk.events_ticket_id')
            ->leftJoin('events as e', 'e.id', '=', 'et.event_id')
            ->select(
                DB::raw("DATE_FORMAT($gateType, '%Y-%m-%d') as $type"),
                'et.title',
                DB::raw('COUNT(gt.ticket_id) as count')
            )
            ->where('e.id', $eventId)
            ->groupBy('et.title', DB::raw("DATE_FORMAT($gateType, '%Y-%m-%d')"))
            ->orderBy('et.title', 'desc')
            ->get();

        if ($date == null) {
            $transformed = [
                'series' => $result->pluck('count')->all(),
                'labels' => $result->pluck('title')->all()
            ];

            return $transformed;
        }

        return $result;
    }
}
