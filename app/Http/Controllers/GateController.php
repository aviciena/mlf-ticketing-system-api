<?php

namespace App\Http\Controllers;

use App\Helpers\HashidHelper;
use App\Http\Resources\GateResource;
use App\Models\Events;
use App\Models\Gate;
use App\Models\GateEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GateController extends BaseController
{
    /**
     * Get All Gates
     */
    public function index(Request $request)
    {
        $eventId = $request->user()->event_id;

        $event = Events::with(['subEvents' => function ($query) {
            $query->select('parent_id', 'id', 'title as name', 'start_date', 'end_date');
        }])->where('id', $eventId)->first('id');

        $eventList = [$event->id];
        if ($event->subEvents && count($event->subEvents) > 0) {
            foreach ($event->subEvents as $subEvent) {
                array_push($eventList, $subEvent->id);
            }
        }

        $query = Gate::with('events')->whereHas('events', function ($query) use ($eventList) {
            $query->whereIn('event_id', $eventList); // Ini merujuk ke kolom di tabel pivot GateEvents
        });

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $gates = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            GateResource::collection($gates),
            'Gate list retrieved successfully',
            $meta
        );
    }

    public function findByEvent(Request $request, $id)
    {
        $eventList = Str::of($id)
            ->explode(',')
            ->map(fn($item) => trim($item))
            ->toArray();

        $query = Gate::with('events')->whereHas('events', function ($query) use ($eventList) {
            $query->whereIn('event_id', $eventList); // Ini merujuk ke kolom di tabel pivot GateEvents
        });

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $gates = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            GateResource::collection($gates),
            'Gate list retrieved successfully',
            $meta
        );
    }

    /**
     * Create Gate
     */
    public function create(Request $request)
    {
        $user = User::find($request->user()->id);

        $validate = $request->validate([
            'description' => 'nullable|string|max:255|unique:gates,description',
            'active' => 'nullable|boolean',
            'event_name' => 'required|string|exists:events,id'
        ]);

        $validate['code'] = strtolower(str_replace(" ", "", $request->description)) . '_' . strtolower($validate['event_name']);
        $validate['created_by'] = $user->name;

        $gate = Gate::create($validate);
        GateEvent::create(['gate_id' => $gate->id, 'event_id' => $validate['event_name']]);

        return $this->sendResponse(
            new GateResource($gate),
            'Gate created successfully',
            null,
            201
        );
    }

    /**
     * Update Gate
     */
    public function update(Request $request, $id)
    {
        $user = User::find($request->user()->id);

        $gate = Gate::findByHashid($id);
        if (!$gate) {
            return response()->json([
                'success' => false,
                'message' => 'Gate not found',
                'data' => []
            ], 422);
        }

        $request->validate([
            'description' => ['nullable', 'string', 'max:255', Rule::unique('gates', 'description')->ignore(HashidHelper::decode($id))],
            'active' => 'nullable|boolean'
        ]);

        $request['code'] = strtolower(str_replace(" ", "", $request->description));
        $request['updated_by'] = $user->name;

        GateEvent::where('gate_id', $gate->id)->update(['event_id' => $request['event_name']]);

        $gate->update($request->all());

        return $this->sendResponse(
            new GateResource($gate),
            'Gate updated successfully'
        );
    }

    /**
     * Delete Gate
     */
    public function delete($id)
    {
        $gate = Gate::findByHashid($id);
        if (!$gate) {
            return response()->json([
                'success' => false,
                'message' => 'Gate not found',
                'data' => []
            ], 422);
        }

        GateEvent::where('gate_id', $gate->id)->delete();
        $gate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gate deleted successfully',
            'data' => []
        ]);
    }
}
