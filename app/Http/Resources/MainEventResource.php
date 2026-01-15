<?php

namespace App\Http\Resources;

use App\Helpers\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isHaveBanners = $this->banners && count($this->banners) > 0;
        $isHaveSubEvents = $this->subEvents && count($this->subEvents) > 0;
        $isHaveEventTickets = $this->eventTickets && count($this->eventTickets) > 0;

        return [
            'id' => Utils::encode($this->id),
            'banners' => $isHaveBanners ? MainEventBannerResource::collection($this->whenLoaded('banners'))[0] : null,
            'title' => $this->title,
            'date' => Utils::formatRange($this->start_date, $this->end_date),
            'hours' => Utils::formatRangeHour($this->start_date, $this->end_date),
            'location' => ['name' => $this->venue->title . ', ' . $this->venue->street, 'maps' => $this->venue->maps],
            'price' => 0,
            'description' => $this->description,
            'tnc' => [],
            'ticket_list' => $isHaveEventTickets ? MainEventTicketResource::collection($this->whenLoaded('eventTickets')) : [],
            'max_ticket' => 20,
            'additional_ticket' => $isHaveSubEvents ? MainEventSubEventResource::collection($this->whenLoaded('subEvents'))[0] : null
        ];
    }
}
