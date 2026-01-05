<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GateTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ticket_number' => $this->ticket ? $this->ticket->id : null,
            'checkin_gate_id' => $this->gate ? $this->gate->hashid : null,
            'checkin_gate' => $this->gate ? $this->gate->description : null,
            'check_in_date' => Carbon::parse($this->check_in_date)->format('d/m/Y H:i'),
            'checkout_gate_id' => $this->checkoutGate ? $this->checkoutGate->hashid : null,
            'checkout_gate' => $this->checkoutGate ? $this->checkoutGate->description : null,
            'check_out_date' => Carbon::parse($this->check_out_date)->format('d/m/Y H:i'),
            'holder' => [
                'name' => $this->ticket->holder->name,
                'category' => $this->ticket->holder->holderCategory->description ?? '-',
                'organization' => $this->ticket->holder->organization ?? '-'
            ],
            'event_ticket' => [
                'id' => $this->ticket->eventTicket?->id,
                'name' => $this->ticket->eventTicket?->title
            ]
        ];
    }
}
