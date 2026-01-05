<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogImportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ticket_id' => $this->ticket_id,
            'active_event_id' => $this->active_event_id,
            'events_ticket' => [
                'id' => $this->events_ticket_id ?? '-',
                'name' => $this->events_ticket_title ?? '-',
            ],
            'message' => $this->message,
            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y H:i:s')
        ];
    }
}
