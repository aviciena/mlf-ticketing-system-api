<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventTicketResource;
use App\Models\EventsTicketCategory;
use App\Models\EventTicket;
use App\Models\GateTicket;
use App\Models\Ticket;
use App\Models\ValidityTicket;
use App\Services\EventTicketIdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventTicketController extends BaseController
{
    // Get All Event Ticket
    public function index(Request $request)
    {
        $eventId = $request->user()->event_id;

        if ($request->has('id') && $request->id != '') {
            $eventId = $request->id;
        }

        $query = EventTicket::with(['event', 'category', 'validityType'])
            ->where('event_id', $eventId)
            ->orderBy('title', 'asc');

        // search by event ticket name
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        $tickets = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            EventTicketResource::collection($tickets),
            'Event Tickets retrieved successfully',
            $meta
        );
    }

    /**
     * Find Ticket Based On Event Ticket ID
     */
    public function find($id)
    {
        $eventTicket = EventTicket::find($id);
        if (!$eventTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Event ticket not found',
                'data' => []
            ], 422);
        }

        $eventTicket = $eventTicket->load(['event', 'category', 'validityType']);
        return response()->json([
            'success' => true,
            'message' => 'Event Ticket found',
            'data' =>  new EventTicketResource($eventTicket)
        ]);
    }

    /**
     * 
     */
    public function create(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string|exists:events,id',
            'title' => ['required', 'string', 'max:255', Rule::unique('event_tickets')->where(function ($query) use ($request) {
                return $query->where('event_id', $request->event_id);
            })],
            'event_ticket_category' => 'required|string|exists:events_ticket_categories,description',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date|after_or_equal:sale_start_date',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'quota' => 'nullable|integer|min:0',
            'price' => 'nullable|integer|min:0',
            'original_price' => 'nullable|integer|min:0',
            'discount_type' => 'nullable|string',
            'discount_amount' => 'nullable|integer|min:0',
            'price_after_discount' => 'nullable|integer|min:0',
            'allow_multiple_checkin' => 'nullable|boolean',
            'validity_type' => 'nullable|string|exists:validity_tickets,description',
            'auto_checkout' => 'nullable|boolean',
            'external_event_ticket_id' => 'nullable|string',
        ]);

        $id = EventTicketIdGenerator::generateUnique($request->event_id);
        $categoryId = EventsTicketCategory::where('description', $request->event_ticket_category)->value('id');
        $validityId = ValidityTicket::where('description', $request->validity_type)->value('id');

        $ticket = EventTicket::create(array_merge(
            $request->all(),
            [
                'id' => $id,
                'event_ticket_category_id' => $categoryId,
                'validity_type_id' => $validityId
            ]
        ));

        return $this->sendResponse(
            new EventTicketResource($ticket->load(['event', 'category', 'validityType'])),
            'Event Ticket created successfully',
            null,
            201
        );
    }

    public function update(Request $request, $id)
    {
        $ticket = EventTicket::find($id);
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Event ticket not found',
                'data' => []
            ], 422);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'event_ticket_category' => 'sometimes|required|string|exists:events_ticket_categories,description',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'sale_start_date' => 'sometimes|nullable|date',
            'sale_end_date' => 'sometimes|nullable|date|after_or_equal:sale_start_date',
            'min_quantity' => 'sometimes|nullable|integer|min:1',
            'max_quantity' => 'sometimes|nullable|integer|min:1',
            'quota' => 'sometimes|nullable|integer|min:0',
            'price' => 'sometimes|nullable|integer|min:0',
            'original_price' => 'sometimes|nullable|integer|min:0',
            'discount_type' => 'sometimes|nullable|string',
            'discount_amount' => 'sometimes|nullable|integer|min:0',
            'price_after_discount' => 'sometimes|nullable|integer|min:0',
            'allow_multiple_checkin' => 'nullable|boolean',
            'validity_type' => 'nullable|string|exists:validity_tickets,description',
            'auto_checkout' => 'sometimes|nullable|boolean',
            'external_event_ticket_id' => 'nullable|string',
        ]);

        $categoryId = EventsTicketCategory::where('description', $request->event_ticket_category)->value('id');
        $validityId = ValidityTicket::where('description', $request->validity_type)->value('id');

        $ticket->update(array_merge($request->all(), [
            'event_ticket_category_id' => $categoryId,
            'validity_type_id' => $validityId
        ]));

        return $this->sendResponse(
            new EventTicketResource($ticket->load(['event', 'category', 'validityType'])),
            'Event Ticket updated successfully'
        );
    }

    public function delete($id)
    {
        $eventTicket = EventTicket::find($id);
        if (!$eventTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Event Ticket not found',
                'data' => []
            ], 422);
        }

        DB::transaction(function () use ($eventTicket) {
            // Get all ticket ID based on event ticket id
            $ticketIds = Ticket::where('events_ticket_id', $eventTicket->id)->pluck('id');

            // Delete all related ticket in gates ticket
            GateTicket::whereIn('ticket_id', $ticketIds)->delete();

            // Delete all related ticket
            Ticket::whereIn('id', $ticketIds)->delete();

            // Delete event ticket
            $eventTicket->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Event Ticket deleted successfully',
            'data' => []
        ]);
    }
}
