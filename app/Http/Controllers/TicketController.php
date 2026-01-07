<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\LogImportResource;
use App\Http\Resources\TicketResource;
use App\Models\Events;
use App\Models\EventTicket;
use App\Models\GateTicket;
use App\Models\Holder;
use App\Models\HolderCategories;
use App\Models\ImportTicketStatus;
use App\Models\LogImportTickets;
use App\Models\PaymentStatus;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use App\Models\ValidityTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TicketController extends BaseController
{
    /**
     * Get All Tickets
     */
    public function index(Request $request)
    {
        $eventId = $request->user()->event_id;

        if ($request->has('id') && $request->id != '') {
            $eventId = $request->id;
        }

        $sortBy = 'created_at';
        $sortOrder = 'desc';

        $query = $tickets = Ticket::with([
            'eventTicket',
            'ticketStatus',
            'paymentStatus',
            'holder',
            'validityTicket',
            'gate',
        ])->whereHas('eventTicket', function ($subQuery) use ($eventId) {
            $subQuery->where('event_id', $eventId);
        })->join('holders', 'holders.id', '=', 'tickets.holder_ticket_id')
            ->select('tickets.*');

        // search by ticket id or holder name
        if ($request->has('search') && $request->search !== '') {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('tickets.id', 'like', "{$searchTerm}%")
                    ->orWhereHas('holder', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Search by event_ticket_id
        if ($request->filled('event_ticket')) {
            $eventTicketIds = is_array($request->event_ticket)
                ? $request->event_ticket
                : explode(',', $request->event_ticket);

            $query->whereIn('events_ticket_id', $eventTicketIds);
        }

        // Search by ticket status
        if ($request->filled('status')) {
            $ticketStatusIds = [];
            $ticketStatusParams = is_array($request->status)
                ? $request->status
                : explode(',', $request->status);

            for ($i = 0; $i < count($ticketStatusParams); $i++) {
                $statusId = TicketStatus::where('code', $ticketStatusParams[$i])->first()->id;
                $ticketStatusIds[] = $statusId;
            }

            $query->whereIn('ticket_status_id', $ticketStatusIds);
        }

        // Search by holder category
        if ($request->filled('category')) {
            $categoryIds = [];
            $categoryParams = is_array($request->category)
                ? $request->category
                : explode(',', $request->category);

            for ($i = 0; $i < count($categoryParams); $i++) {
                $categoryId = HolderCategories::where('code', $categoryParams[$i])->first()->id;
                $categoryIds[] = $categoryId;
            }

            $query->whereHas('holder', function ($subQuery) use ($categoryIds) {
                $subQuery->whereIn('category_id', $categoryIds);
            });
        }

        // Sort By
        if ($request->filled('sort_by')) {
            [$field, $direction] = explode(',', $request->sort_by);
            $sortOrder = $direction;

            switch ($field) {
                case 'ticket_id':
                    $sortBy = 'id';
                    break;
                case 'name':
                    $sortBy = 'holders.name';
                    break;
                case 'event_ticket':
                    $sortBy = 'events_ticket_id';
                    break;
                case 'category':
                    $sortBy = 'holders.category_id';
                    break;
                case 'organization':
                    $sortBy = 'holders.organization';
                    break;
                default:
                    $sortBy = 'created_at';
                    break;
            }
        }

        // Sorting
        $query->orderBy($sortBy, $sortOrder);

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $tickets = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            TicketResource::collection($tickets),
            'Ticket list retrieved successfully',
            $meta
        );
    }

    public function find($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'data' => []
            ], 422);
        }

        $ticket = $ticket->load([
            'eventTicket',
            'ticketStatus',
            'paymentStatus',
            'holder',
            'validityTicket',
            'gate'
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Ticket found',
            'data' =>  new TicketResource($ticket)
        ]);
    }

    public function create(CreateTicketRequest $request)
    {
        $activeEventId = $request->user()->event_id;
        $userName = User::find($request->user()->id)->username;
        return $this->doCreateTicket($request->all(), false, $activeEventId, $userName);
    }

    public function import(CreateTicketRequest $request)
    {
        $activeEventId = $request->user()->event_id;
        $userName = User::find($request->user()->id)->username;
        return $this->doCreateTicket($request->all(), true, $activeEventId, $userName);
    }

    public function update(UpdateTicketRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $ticket = Ticket::with(['ticketStatus', 'paymentStatus'])->find($id);

            if (!$ticket) {
                return $this->sendError('Ticket not found', [], 422);
            }

            $holder = Holder::findByHashid($request['holder']['id']);

            $holderCategoryId = HolderCategories::where('description', $request['holder']['category'])->value('id');

            // Update Holder
            $holder = $holder->update([
                'name' => $request['holder']['name'],
                'category_id' => $holderCategoryId,
                'photo' => $request['holder']['photo'] ?? null,
                'organization' => $request['holder']['organization'] ?? null,
                'position' => $request['holder']['position'] ?? null,
                'dob' => $request['holder']['dob'] ?? null,
                'mobile_phone' => $request['holder']['phone'] ?? null,
                'email' => $request['holder']['email'] ?? null,
                'address' => $request['holder']['address'] ?? null,
                'city' => $request['holder']['city'] ?? null,
            ]);

            $eventTicket = EventTicket::where('id', $request['events_ticket_id'])->first();
            $ticketStatus = $request['ticket_status'] ?? $ticket->ticketStatus->description;

            $paymentStatus = $request['payment_status'] ?? $ticket->paymentStatus->description;
            $paymentId = PaymentStatus::where('description', $paymentStatus)->value('id');

            $validity = ValidityTicket::where('id', $eventTicket['validity_type_id'])->first();
            if ($validity['code'] != "ad") {
                $now = Carbon::now();
                $endDate = Carbon::parse($eventTicket['end_date']);
                if ($now->greaterThan($endDate)) {
                    $ticketStatus = 'expired';
                }
            }

            $ticketStatusId = TicketStatus::where('description', $ticketStatus)->value('id');
            $user = User::find($request->user()->id);

            $data = [
                'events_ticket_id' => $request['events_ticket_id'],
                'ticket_status_id' => $ticketStatusId,
                'payment_status_id' => $paymentId,
                'validity_ticket_id' => $validity['id'],
                'validity_start_date' => $eventTicket['start_date'],
                'validity_end_date' => $validity['code'] != "ad" ? $eventTicket['end_date'] : null,
                'allow_multiple_checkin' => $eventTicket['allow_multiple_checkin'],
                'updated_by' => $user->username
            ];

            // update Ticket
            $ticket->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully',
                'data' => [
                    'ticket' => TicketResource::collection([$ticket])[0]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return $this->sendError('Ticket Number is not found', [], 422);
        }

        if (GateTicket::where('ticket_id', $id)->exists()) {
            return $this->sendError('Unable to delete, ticket number already use', [], 400);
        }

        $eventTicket = EventTicket::find($ticket->events_ticket_id);
        $eventTicket->increment('quota');

        $ticket->delete();

        return $this->sendResponse([], 'Ticket Number deleted successfully');
    }

    /**
     * Delete All Ticket based on event id
     */
    public function deleteAll(Request $request)
    {
        $eventId = $request->event_id;
        $eventsTicket = Events::find($eventId);

        if (!$eventsTicket) {
            return $this->sendError('Event is not found', [], 422);
        }

        // Get all events ticket ID based on event id
        $eventTicketIds = EventTicket::where('event_id', $eventId)->pluck('id');

        // Get all ticket ID based on event ticket id
        $ticketIds = Ticket::whereIn('events_ticket_id', $eventTicketIds)->pluck('id');

        if (count($ticketIds) == 0) {
            return $this->sendError('No Ticket Number Deleted.', [], 200);
        }

        // Delete all gates ticket based on events ticket id
        GateTicket::whereIn('ticket_id', $ticketIds)->delete();

        // Delete Log Import Ticket based on event ticket id
        LogImportTickets::whereIn('events_ticket_id', $eventTicketIds)->delete();

        // Delete all ticket based on events ticket id
        Ticket::whereIn('events_ticket_id', $eventTicketIds)->delete();

        return $this->sendResponse([], 'All Ticket Number deleted successfully.');
    }

    /**
     * Import Tickets from csv file
     */
    public function upload(Request $request)
    {
        $userName = User::find($request->user()->id)->username;
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'event_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $lines = file($path);

        // use semicolons as delimiters
        $data = array_map(function ($line) {
            return str_getcsv($line, ';');
        }, $lines);

        // remove blank lines (if any)
        $data = array_filter($data, function ($row) {
            return array_filter($row); // Eliminate rows that have all empty elements
        });

        // Optional: Skip header if needed
        $header = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);
        $importTickets = [];

        // Process data (example)
        foreach ($rows as $row) {
            $rowData = array_combine($header, $row);
            $ticket = [
                "event_id" => $request->event_id,
                "ticket_id" => $rowData["Ticket Id"] ?? null,
                "events_ticket_id" => $rowData["Event Ticket ID"] ?? null,
                "ticket_status" => $rowData["Ticket Status"] ?? null,
                "payment_status" => $rowData["Payment Status"] ?? null,
                "holder" => [
                    "name" => !empty($rowData["Name"]) ? $rowData["Name"] : null,
                    "category" => !empty($rowData["Ticket Category"]) ? $rowData["Ticket Category"] : null,
                    "organization" => !empty($rowData["Organization"]) ? $rowData["Organization"] :  null,
                    "position" => !empty($rowData["Position"]) ? $rowData["Position"] : null,
                    "dob" => !empty($rowData["DOB"]) ? $rowData["DOB"] : null,
                    "phone" => !empty($rowData["Phone"]) ? $rowData["Phone"] : null,
                    "email" => !empty($rowData["Email"]) ? $rowData["Email"] : null,
                    "address" => !empty($rowData["Address"]) ? $rowData["Address"] : null,
                    "city" => !empty($rowData["City"]) ? $rowData["City"] : null,
                ]
            ];

            $importTickets[] = $ticket;
        }

        return $this->doCreateTicket($importTickets, true, $request->event_id, $userName);
    }

    /**
     * Create Ticket or multiple ticket and or create or update import tickets status
     */
    public function doCreateTicket($ticketList, $isImport = false, $activeEventId, $userName)
    {
        try {
            DB::beginTransaction();

            $createdTickets = [];
            $logImportTickets = [];

            foreach ($ticketList as $ticketData) {
                if ($isImport) {
                    $eventTicket = EventTicket::where('event_id', $ticketData['event_id'])
                        ->where('id', $ticketData['events_ticket_id'])
                        ->first();
                } else {
                    $eventTicket = EventTicket::where('id', $ticketData['events_ticket_id'])->first();
                }

                //Check if event ticket exist or not
                if (!$eventTicket) {
                    $logImportTicket = LogImportTickets::create([
                        'active_event_id' => $activeEventId,
                        'ticket_id' => $ticketData['ticket_id'],
                        'events_ticket_id' => $ticketData['events_ticket_id'],
                        'message' => 'Event Ticket not found.'
                    ]);

                    $logImportTickets[] = $logImportTicket;
                    continue;
                } else {
                    $eventTicket->decrement('quota');
                }

                //Check if ticket is not empty and already exist or not
                if (!empty($ticketData['ticket_id'])) {
                    $isExist = Ticket::where('id', $ticketData['ticket_id'])->exists();

                    if ($isExist) {
                        $logImportTicket = LogImportTickets::create([
                            'active_event_id' => $activeEventId,
                            'ticket_id' => $ticketData['ticket_id'],
                            'events_ticket_id' => $eventTicket['id'],
                            'events_ticket_title' => $eventTicket['title'],
                            'message' => 'Duplicate ticket number'
                        ]);
                        $logImportTickets[] = $logImportTicket;
                        continue;
                    }
                }

                // Create Holder
                $holderCategoryId = HolderCategories::where('description', $ticketData['holder']['category'])->value('id');
                $holder = Holder::create([
                    'category_id' => $holderCategoryId,
                    'name' => $ticketData['holder']['name'],
                    'mobile_phone' => $ticketData['holder']['phone'] ?? null,
                    'email' => $ticketData['holder']['email'] ?? null,
                    'sex' => $ticketData['holder']['sex'] ?? null,
                    'organization' => $ticketData['holder']['organization'] ?? null,
                    'position' => $ticketData['holder']['position'] ?? null,
                ]);

                $paymentId = $eventTicket['event_ticket_category_id'];
                $validity = ValidityTicket::where('id', $eventTicket['validity_type_id'])->first();

                $ticketStatus = $ticketData['ticket_status'] ?? "Issued";
                if ($validity['code'] != "ad") {
                    $now = Carbon::now();
                    $endDate = Carbon::parse($eventTicket['end_date']);
                    if ($now->greaterThan($endDate)) {
                        $ticketStatus = 'Expired';
                    }
                }

                $ticketStatusId = TicketStatus::where('description', $ticketStatus)->value('id');

                $data = [
                    'events_ticket_id' => $ticketData['events_ticket_id'],
                    'ticket_status_id' => $ticketStatusId,
                    'payment_status_id' => $paymentId,
                    'validity_ticket_id' => $validity['id'],
                    'validity_start_date' => $eventTicket['start_date'],
                    'validity_end_date' => $validity['code'] != "ad" ? $eventTicket['end_date'] : null,
                    'allow_multiple_checkin' => $eventTicket['allow_multiple_checkin'],
                    'holder_ticket_id' => $holder->id,
                    'created_by' => $userName
                ];

                $ticketId = !empty($ticketData['ticket_id']) ? $ticketData['ticket_id'] : $this->generateRandomString();

                // Create Ticket
                $ticket = Ticket::create(array_merge($data, ['id' => $ticketId]));

                $createdTickets[] = $ticket;
            }

            DB::commit();

            $message = $isImport ? "Import Ticket successfully" : 'Ticket created successfully';
            $message = $isImport && count($createdTickets) == 0 ? 'No Ticket imported' : $message;

            if ($isImport) {
                $lastImportTicketStatus = ImportTicketStatus::orderBy('id', 'desc')->first();

                if ($lastImportTicketStatus) {
                    $lastImportTicketStatus->update([
                        'last_import_date' => now()
                    ]);
                } else {
                    ImportTicketStatus::create([
                        'last_import_date' => now(),
                    ]);
                }
            }

            $responseObj = [
                'success' => true,
                'message' => $message,
                'data' => [
                    'tickets' => TicketResource::collection($createdTickets)
                ]
            ];

            if ($isImport) {
                $responseObj['data']['duplicate_entries'] = LogImportResource::collection($logImportTickets);
            }

            return response()->json($responseObj, 201);
        } catch (\Exception $e) {
            $message = $isImport ? "Failed to import tickets" : 'Failed to create tickets';
            DB::rollBack();
            Log::error('Error Create or Import Tickets ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $message,
                'detail' => $e->getMessage(),
                'ticketList' => $ticketList
            ], 500);
        }
    }

    private function generateRandomString()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->format('m');
        $date = $currentDate->format('d');
        $hour = $currentDate->format('H');

        $randomChars = Str::random(6);

        return $year . $month . $date . $hour . strtoupper($randomChars);
    }
}
