<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Events;
use App\Models\EventStatus;
use App\Models\EventTicket;
use App\Models\GateTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EventIdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

class EventController extends BaseController
{
    //Get All Events
    public function index(Request $request)
    {
        $query = Events::with(['venue', 'status'])
            ->whereNull('parent_id')
            ->orderBy('status_id', 'asc')
            ->orderBy('start_date', 'asc');

        // search by event name
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

        $events = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            EventResource::collection($events),
            'Events retrieved successfully.',
            $meta
        );
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'venue_id' => 'required|string|exists:venues,id',
            'title' => ['required', 'string', 'max:255', Rule::unique('events')->where(function ($query) use ($request) {
                return $query->where('venue_id', $request->venue_id);
            })],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'icon' => 'nullable|string',
            'auto_sync' => 'nullable|boolean',
            'is_sync_interval' => 'nullable|boolean',
            'sync_query' => 'nullable|string',
            'event_external_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $today = Carbon::now();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $statusCode = 'active';

        if ($today->lessThan($startDate)) {
            $statusCode = 'not_started';
        } else if ($today->greaterThan($endDate)) {
            $statusCode = 'completed';
        }

        $statusId = EventStatus::where('code', $statusCode)->first()['id'];
        $id = EventIdGenerator::generateUnique();
        $user = User::find($request->user()->id);
        $userName = $user->name;

        $validated['id'] = $id;
        $validated['status_id'] = $statusId;
        $validated['created_by'] = $userName;

        // Membersihkan HTML sebelum disimpan atau ditampilkan dari tag script
        $validated['description'] = Purifier::clean($request->description);

        $event = Events::create($validated);

        return $this->sendResponse(
            new EventResource($event->load(['venue', 'status'])),
            'Event created successfully.',
            null,
            201
        );
    }

    public function update(Request $request, $id)
    {
        $event = Events::find($id);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'data' => []
            ], 422);
        }

        $request->validate([
            'venue_id' => 'sometimes|string|exists:venues,id',
            'title' => ['sometimes', 'string', 'max:255', Rule::unique('events')->ignore($event->id)->where(function ($query) use ($request) {
                return $query->where('venue_id', $request->venue_id);
            })],
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'icon' => 'nullable|string',
            'auto_sync' => 'nullable|boolean',
            'is_sync_interval' => 'nullable|boolean',
            'sync_query' => 'nullable|string',
            'event_external_id' => 'nullable|string|max:255',
            'endpoint' => 'nullable|string',
            'api_key' => 'nullable|string',
        ]);

        $today = Carbon::now();
        $startDate = Carbon::parse($request->start_date ? $request->start_date : $event->start_date);
        $endDate = Carbon::parse($request->end_date ? $request->end_date : $event->end_date);
        $statusCode = 'active';

        if ($today->lessThan($startDate)) {
            $statusCode = 'not_started';
        } else if ($today->greaterThan($endDate)) {
            $statusCode = 'completed';
        }

        $statusId = EventStatus::where('code', $statusCode)->first()['id'];

        $event->update(array_merge(
            $request->all(),
            ['status_id' => $statusId]
        ));

        return $this->sendResponse(
            new EventResource($event->load(['venue', 'status'])),
            'Event updated successfully.'
        );
    }

    public function delete($id)
    {
        $event = Events::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'data' => []
            ], 422);
        }

        // Get all user ID based on event id
        $users = User::where('event_id', $event->id)->pluck('id');
        User::whereIn('id', $users)->update(['event_id' => null]);

        // Get all event tickets id based on event id
        $eventTicketsId = EventTicket::where('event_id', $event->id)->pluck('id');

        // Get all ticket ID based on event ticket id
        $ticketIds = Ticket::whereIn('events_ticket_id', $eventTicketsId)->pluck('id');

        // Delete all related ticket in gates ticket
        GateTicket::whereIn('ticket_id', $ticketIds)->delete();

        // Delete all related ticket
        Ticket::whereIn('id', $ticketIds)->delete();

        // Delete event ticket
        EventTicket::whereIn('id', $eventTicketsId)->delete();

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.',
            'data' => []
        ]);
    }
}
