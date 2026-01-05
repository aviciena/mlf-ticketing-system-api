<?php

namespace App\Http\Controllers;

use App\Helpers\HashidHelper;
use App\Http\Requests\CheckInTicketRequest;
use App\Http\Requests\CheckOutTicketRequest;
use App\Http\Resources\GateTicketResource;
use App\Models\GateTicket;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GateTicketController extends BaseController
{
    /**
     * Get All Gate Ticket List
     */

    public function index(Request $request)
    {
        $query = GateTicket::with(['gate', 'checkoutGate', 'ticket']);

        if ($request->has('checkin_gate_id')) {
            $gateId = HashidHelper::decode($request->checkin_gate_id);
            $query->where('checkin_gate_id', $gateId);
        }

        if ($request->has('checkout_gate_id')) {
            $gateId = HashidHelper::decode($request->checkout_gate_id);
            $query->where('checkout_gate_id', $gateId);
        }

        if ($request->has('ticket_id')) {
            $ticketId = $request->ticket_id;
            $query->where('ticket_id', $ticketId);
        }

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        $gateTickets = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            GateTicketResource::collection($gateTickets),
            'Event Gate Tickets retrieved successfully',
            $meta
        );
    }

    /**
     * CheckIn Ticket
     */
    public function checkIn(CheckInTicketRequest $request)
    {
        $data = [
            'ticket_id' => $request->ticket_id,
            'checkin_gate_id' => HashidHelper::decode($request->checkin_gate_id),
            'check_in_date' => now()
        ];

        $ticket = Ticket::find($request->ticket_id);

        $errorMessage = $this->validateTicket($ticket, 'checkin');
        if (!empty($errorMessage)) {
            return $this->sendError($errorMessage);
        }

        $eventTicket = $ticket->eventTicket()->first();
        $ticketStatus = $ticket->ticketStatus()->first();

        if ($ticketStatus['code'] == 'check_in' && ($eventTicket['auto_checkout'] == 1)) {
            // Find last record based on ticket_id
            $gateTicket = GateTicket::where('ticket_id', $data['ticket_id'])
                ->orderByDesc('id')
                ->first();

            // Force checkout
            $gateTicket->update([
                'ticket_id' => $request->ticket_id,
                'checkout_gate_id' => HashidHelper::decode($request->checkin_gate_id),
                'check_out_date' => now(),
            ]);
        }

        $gateTicket = GateTicket::create($data);
        $ticketStatusId = TicketStatus::where('code', 'check_in')->value('id');

        $ticketUpdate = [
            'ticket_status_id' => $ticketStatusId
        ];

        $validityTicket = $ticket->validityTicket()->first();
        if (($validityTicket['code'] == "ad") && empty($ticket['validity_end_date'])) {
            $ticketUpdate['validity_end_date'] = Carbon::now()->format('y-m-d') . ' ' . '21:00:00';
        }
        $ticket->update($ticketUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Ticket checkin successfully',
            'data' => new GateTicketResource($gateTicket)
        ], 200);
    }

    /**
     * CheckOut Ticket based on last record by given ticket id
     */
    public function checkOut(CheckOutTicketRequest $request)
    {
        $ticket = Ticket::find($request->ticket_id);

        $errorMessage = $this->validateTicket($ticket, 'checkout');
        if (!empty($errorMessage)) {
            return $this->sendError($errorMessage);
        }

        $data = [
            'ticket_id' => $request->ticket_id,
            'checkout_gate_id' => HashidHelper::decode($request->checkout_gate_id),
            'check_out_date' => now(),
        ];

        // Find last record based on ticket_id
        $gateTicket = GateTicket::where('ticket_id', $data['ticket_id'])
            ->orderByDesc('id')
            ->first();

        if (!$gateTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Gate Ticket not found for given ticket_id',
            ], 422);
        }

        $gateTicket->update($data);
        $ticketStatusId = TicketStatus::where('code', 'check_out')->value('id');
        $ticket->update(['ticket_status_id' => $ticketStatusId]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket checkout successfully',
            'data' => new GateTicketResource($gateTicket)
        ]);
    }

    private function validateTicket($ticket, $type): string
    {
        $event = $ticket->eventTicket()->first()->event()->first();
        $eventTicket = $ticket->eventTicket()->first();
        $ticketStatus = $ticket->ticketStatus()->first();
        $eventStartDate = Carbon::parse($event['start_date']);
        $eventEndDate = Carbon::parse($event['end_date']);
        $startDate = Carbon::parse($eventTicket['start_date']);
        $endDate = Carbon::parse($eventTicket['end_date']);
        $validityEndDate = Carbon::parse($ticket['validity_end_date']);
        $today = Carbon::now();

        if ($today->greaterThan($eventEndDate)) {
            return "Event sudah selesai. Selesai pada tanggal {$eventEndDate->format('d/m/Y')}.";
        } else if ($today->lessThan($eventStartDate)) {
            return "Event belum dimulai. Dimulai pada tanggal {$eventStartDate->format('d/m/Y')}.";
        } else if ($today->toDateString() < $startDate->toDateString()) {
            return "Tiket ini belum dapat digunakan. Dimulai pada tanggal {$startDate->format('d/m/Y')}.";
        } else if (!empty($ticket['validity_end_date']) && ($today->greaterThan($validityEndDate))) {
            return "Tiket sudah kadarluasa. Tiket hanya berlaku sampai tanggal {$validityEndDate->format('d/m/Y H:i')}.";
        } else if (($today->format('H:i')) < ($startDate->format('H:i'))) {
            return "Tiket ini belum dapat digunakan. Dimulai pada jam {$startDate->format('H:i')}.";
        } else if ($ticketStatus['code'] == 'booked') {
            return 'Status pembayaran belum selesai.';
        } else if ($endDate->isPast()) {
            return 'Tiket sudah kadarluasa. Tiket hanya berlaku sampai tanggal' . $endDate->format('d/m/Y');
        } else if ($type == "checkin" && $ticketStatus['code'] == 'check_in' && ($eventTicket['auto_checkout'] == 0)) {
            return 'Harap check-out terlebih dahulu sebelum check-in kembali.';
        } else if ($type == "checkout" && $ticketStatus['code'] == 'check_out') {
            return "Tiket {$ticket['id']} sudah checked-out.";
        } else if ($type == "checkout" && $ticketStatus['code'] == 'issued') {
            return 'Harap check-in terlebih dahulu.';
        }

        return '';
    }
}
