<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventTicketRequest;
use App\Http\Resources\EventTicketResource;
use App\Models\EventsTicketCategory;
use App\Models\EventTicket;
use App\Models\GateTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Models\ValidityTicket;
use App\Services\EventTicketIdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function create(CreateEventTicketRequest $request)
    {
        $validated = $request->validated();

        $id = EventTicketIdGenerator::generateUnique($request->event_id);
        $categoryId = EventsTicketCategory::where('description', $request->event_ticket_category)->value('id');
        $validityId = ValidityTicket::where('description', $request->validity_type)->value('id');

        $user = User::find($request->user()->id);
        // Membersihkan HTML sebelum disimpan atau ditampilkan dari tag script
        $validated['description'] = clean($request->description);

        $ticket = EventTicket::create(array_merge(
            $validated,
            [
                'id' => $id,
                'event_ticket_category_id' => $categoryId,
                'validity_type_id' => $validityId,
                'created_by' => $user->username
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

        $validated = $request->validate([
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
            'description' => 'nullable|string',
        ]);

        $categoryId = EventsTicketCategory::where('description', $request->event_ticket_category)->value('id');
        $validityId = ValidityTicket::where('description', $request->validity_type)->value('id');
        $user = User::find($request->user()->id);

        // Membersihkan HTML sebelum disimpan atau ditampilkan dari tag script
        $validated['description'] = clean($validated['description'], [
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
        ]);

        $ticket->update(array_merge($validated, [
            'event_ticket_category_id' => $categoryId,
            'validity_type_id' => $validityId,
            'updated_by' => $user->username
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
