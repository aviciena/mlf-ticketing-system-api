<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Events;
use App\Models\User;
use App\Models\Venues;
use App\Services\VenueIdGenerator;
use Illuminate\Http\Request;

class VenueController extends BaseController
{
    // Get All Venue List
    public function index(Request $request)
    {
        $query = Venues::with(['events'])->orderBy('title', 'asc');

        // search by title OR city
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('city', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $venues = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            VenueResource::collection($venues),
            'Venue list retrieved successfully.',
            $meta
        );
    }

    public function create(CreateVenueRequest $request)
    {
        $validated = $request->validated();
        $user = User::find($request->user()->id);
        $validated['id'] = VenueIdGenerator::generate($validated['title']);
        $validated['created_by'] = $user->username;

        $venue = Venues::create($validated);

        return $this->sendResponse(
            new VenueResource($venue),
            'Venue created successfully.',
            201
        );
    }

    public function update(UpdateVenueRequest $request, $id)
    {
        $venue = Venues::find($id);

        if (!$venue) {
            return $this->sendError('Venue not found', [], 422);
        }

        $venue->update($request->validated());

        return $this->sendResponse(
            new VenueResource($venue),
            'Venue updated successfully.'
        );
    }

    public function delete($id)
    {
        $venue = Venues::find($id);

        if (!$venue) {
            return $this->sendError('Venue not found', [], 422);
        }

        if (Events::where('venue_id', $id)->exists()) {
            return $this->sendError('Unable to delete, venue is used by event(s)', [], 400);
        }

        $venue->delete();

        return $this->sendResponse([], 'Venue deleted successfully.');
    }
}
