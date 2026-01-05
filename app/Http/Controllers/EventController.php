<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\SubEventResource;
use App\Models\BannerEvents;
use App\Models\Events;
use App\Models\EventStatus;
use App\Models\EventTicket;
use App\Models\GateTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EventIdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

class EventController extends BaseController
{
    //Get All Events
    public function index(Request $request)
    {
        $query = Events::with(['venue', 'status', 'subEvents', 'banners'])
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

    //Find Sub Events
    public function findSubEvent($id)
    {
        $subEvent = Events::with(["parentEvent", "banners"])->find($id);
        if (!$subEvent) {
            return response()->json([
                'success' => false,
                'message' => 'Sub Event not found',
                'data' => []
            ], 422);
        }

        return $this->sendResponse(
            new SubEventResource($subEvent),
            'Sub-Event retrieved successfully.'
        );
    }

    public function create(CreateEventRequest $request)
    {
        $message = 'Event created successfully.';

        $validated = $request->validated();

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

        if (isset($validated['main_event_id'])) {
            $validated["parent_id"] = $validated['main_event_id'];
            $message = 'Sub-Event created successfully.';
        }
        $event = Events::create($validated);

        if ($validated['banners']) {
            // Get all id from banners data
            $bannersId = collect($validated['banners'])->pluck('id')->toArray();

            // Update banners events id
            BannerEvents::whereIn('file_name_id', $bannersId)
                ->update(['events_id' => $event->id]);
        }

        return $this->sendResponse(
            new EventResource($event->load(['venue', 'status', 'banners'])),
            $message,
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

        $validated = $request->validate([
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
            'description' => 'nullable|string',
            'banners' => 'nullable|array',
            // Memastikan setiap item di dalam array banners memiliki 'id'
            'banners.*.id' => 'required|string',
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

        // Membersihkan HTML sebelum disimpan atau ditampilkan dari tag script
        $validated['description'] = Purifier::clean($request->description);

        $event->update(array_merge(
            $validated,
            ['status_id' => $statusId]
        ));

        if ($validated['banners']) {
            // Get all id from banners data
            $bannersId = collect($validated['banners'])->pluck('id')->toArray();

            // Update banners events id
            BannerEvents::whereIn('file_name_id', $bannersId)
                ->update(['events_id' => $event->id]);
        }


        return $this->sendResponse(
            new EventResource($event->load(['venue', 'status', 'banners'])),
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

        // Delete Banners Event
        $paths = BannerEvents::where('events_id', $event->id)->pluck('path');

        // 1. Siapkan path lengkap untuk semua file
        $fullPaths = collect($paths)->map(fn($path) => $path)->toArray();

        // 2. Hapus semua file sekaligus dari storage
        Storage::disk('public')->delete($fullPaths);

        // 3. Hapus semua record dari database dengan satu query
        BannerEvents::where('events_id', $event->id)->delete();

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.',
            'data' => []
        ]);
    }

    public function upload(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 2. Cek apakah ada file yang diunggah
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Memberikan nama unik untuk file
            $filename = time() . '_' . $file->getClientOriginalName();

            // Simpan ke folder 'uploads' di disk 'public'
            $path = $file->storeAs('uploads', $filename, 'public');
            BannerEvents::create([
                'file_name_id' => pathinfo($filename, PATHINFO_FILENAME),
                'file_name' => $file->getClientOriginalName(),
                'path' => $path
            ]);

            // 3. Kembalikan respon sukses
            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diunggah',
                'id' => pathinfo($filename, PATHINFO_FILENAME),
                'name' =>  $file->getClientOriginalName()
            ], 201);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }

    public function destroy($id)
    {
        // 1. Cari data di database
        $banner = BannerEvents::where('file_name_id', $id)->firstOrFail();

        if (!$banner) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // 2. Cek apakah ada file yang tersimpan di kolom 'path'
        if ($banner->path) {
            // Hapus file dari disk 'public'
            if (Storage::disk('public')->exists($banner->path)) {
                Storage::disk('public')->delete($banner->path);
            }
        }

        // 3. Hapus data di database atau kosongkan kolom gambarnya saja
        // Jika ingin hapus baris data:
        $banner->delete();

        // Atau jika hanya ingin menghapus gambarnya saja tapi user tetap ada:
        // $user->update(['image' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus!'
        ], 200);
    }
}
