<?php

namespace App\Http\Resources;

use App\Models\GateTicket;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusCode = $this->ticketStatus?->code;
        $statusDescription = $this->ticketStatus?->description;

        $isExpired = $statusCode !== "booked" && $statusCode != "issued";
        Carbon::setLocale('id');
        $validateTicket = Carbon::parse($this->eventTicket?->start_date)->format('d/m/Y H:i') . '-' . Carbon::parse($this->eventTicket?->end_date)->format('d/m/Y H:i');

        $now = Carbon::now();
        if ($now->greaterThan($this->eventTicket?->end_date)) {
            $isExpired = true;

            $ticketStatus = TicketStatus::where('code', 'expired')->first();
            //check and update the ticket when status code is still issued
            if ($statusCode == "issued") {
                Ticket::where('id', $this->id)->update(['ticket_status_id' => $ticketStatus->id]);
            }

            $statusCode = $ticketStatus->code;
            $statusDescription = $ticketStatus->description;
        }

        // Get last data from gate_tickets table
        $gateTicket = GateTicket::where('ticket_id', $this->id)
            ->orderByDesc('id')
            ->first();

        return [
            'ticket_number' => $this->id,
            'events_ticket_name' => $this->eventTicket?->title,
            'validate_date' => $validateTicket,
            'event_ticket' => [
                'id' => $this->eventTicket?->id,
                'name' => $this->eventTicket?->title
            ],
            'event_name' => $this->eventTicket?->event->title,
            'status' => [
                'code' => $statusCode,
                'description' => $statusDescription
            ],
            'payment' => [
                'code' => $this->paymentStatus?->code,
                'description' => $this->paymentStatus?->description
            ],
            'holder' => [
                'id' => $this->holder?->hashid,
                'name' => $this->holder?->name,
                'category' => $this->holder?->holderCategory?->description,
                'organization' => $this->holder?->organization ?? '-',
                'position' => $this->holder?->position ?? '-',
                'dob' => $this->holder?->dob ? Carbon::parse($this->holder?->dob)->translatedFormat('d F Y') : '-',
                'mobile_phone' => $this->holder?->mobile_phone ?? '-',
                'email' => $this->holder?->email ?? '-',
                'address' => $this->holder?->address ?? '-',
                'city' => $this->holder?->city ?? '-',
                'photo' => $this->holder?->photo ?? '-',
            ],
            'check_in_date' => $gateTicket?->check_in_date ? Carbon::parse($gateTicket?->check_in_date)->format('d/m/Y H:i') : '-',
            'check_out_date' => $gateTicket?->check_out_date ? Carbon::parse($gateTicket?->check_out_date)->format('d/m/Y H:i') : '-',
            'allow_multiple_checkin' => $this->eventTicket?->allow_multiple_checkin == 1,
            'is_used' => $this->gateTickets()->exists(),
            'is_expired' => $isExpired,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
