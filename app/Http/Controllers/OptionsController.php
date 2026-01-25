<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventTicketsOptionsResource;
use App\Http\Resources\StatusResource;
use App\Models\Events;
use App\Models\EventTicket;
use App\Models\HolderCategories;
use App\Models\PaymentStatus;
use App\Models\TicketStatus;
use App\Models\ValidityTicket;
use Illuminate\Http\Request;

class OptionsController extends BaseController
{
    // Get Options
    public function index(Request $request)
    {
        $user = $request->user();
        $eventId = $request->user()->event_id;

        if ($request->has('id') && $request->id != '') {
            $eventId = $request->id;
        }

        $event = Events::with(['subEvents' => function ($query) {
            $query->select('parent_id', 'id', 'title as name', 'start_date', 'end_date');
        }], 'status')->where('id', $user->event_id)->first();
        $eventList = Events::selectRaw('id, title')->get();
        $eventTicket = EventTicket::with('validityType')->where('event_id', $eventId)->get();
        $paymentStatus = PaymentStatus::orderBy('description', 'asc')->get();
        $ticketStatus = TicketStatus::whereIn('code', ['booked', 'issued'])->get();
        $validityTickets = ValidityTicket::whereIn('code', ['sd', 'ad', 'adt'])->get();
        $holderCategories = HolderCategories::all();
        $status = TicketStatus::whereNotIn('code', ['canceled'])->get();

        $subEvents = null;
        if (isset($event->subEvents) && count($event->subEvents) > 0) {
            $subEvents = $event->subEvents->toArray();
            $parentData = [
                'id' => $event->id,
                'name' => $event->title,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date
            ];
            array_unshift($subEvents, $parentData);
        }

        return $this->sendResponse(
            [
                'event' => [
                    'id' => $event->id ?? null,
                    'name' => $event->title ?? null,
                    'start_date' => $event->start_date ?? null,
                    'end_date' => $event->end_date ?? null,
                    'is_completed' => isset($event->status) ? $event->status->code == "completed" : false,
                    'sub_events' => $subEvents,
                ],
                'event_list' => $eventList,
                'event_tickets' => EventTicketsOptionsResource::collection($eventTicket),
                'payment_status' => StatusResource::collection($paymentStatus),
                'ticket_status' => StatusResource::collection($ticketStatus),
                'validity_ticket' => StatusResource::collection($validityTickets),
                'categories' => StatusResource::collection($holderCategories),
                'status' => StatusResource::collection($status),
            ],
            'Options retrieved successfully.',
        );
    }
}
