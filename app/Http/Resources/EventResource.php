<?php

namespace App\Http\Resources;

use App\Helpers\Utils;
use App\Models\Event;
use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $today = Carbon::now();
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $isEventCompleted = $today->greaterThan($endDate);

        if ($today->greaterThan($startDate) && $this->status->code === "not_started") {
            $this->status->code = 'active';
            $this->status->description = 'Active';

            Events::where('id', $this->id)->update(['status_id' => 2]);
        }

        if ($isEventCompleted && ($this->status->code === "not_started" || $this->status->code === "active")) {
            $this->status->code = 'completed';
            $this->status->description = 'Completed';

            Events::where('id', $this->id)->update(['status_id' => 3]);
        }

        $isHaveSubEvents = count($this->subEvents) > 0;
        $isHaveBanners = count($this->banners) > 0;

        return [
            'id' => $this->id,
            'venue_id' => $this->venue->id ?? null,
            'venue' => $this->venue->title ?? null,
            'status' => [
                'code' => $this->status->code ?? null,
                'description' => $this->status->description ?? null,
            ],
            'event_name' => $this->title,
            'organizer' => $this->organizer,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'date_str' => Utils::formatRange($this->start_date, $this->end_date),
            'is_sync' => $this->auto_sync == 1,
            'is_sync_interval' => $this->is_sync_interval == 1,
            'sync_query' => $this->sync_query,
            'event_external_id' => $this->event_external_id,
            'is_default_str' => $request->user()->event_id == $this->id ? "Ya" : "-",
            'is_disable_sync' => $isEventCompleted,
            'is_completed' => $isEventCompleted,
            'endpoint' => $this->endpoint,
            'interval' => $this->interval,
            'api_key' => $this->api_key,
            'description' => $this->description,
            'sub_events' => $isHaveSubEvents ? EventResource::collection($this->whenLoaded('subEvents')) : null, // Memanggil relasi sub_events hanya jika sudah di-load (Eager Loading)
            'banners' => $isHaveBanners ? BannerEventsResource::collection($this->whenLoaded('banners')) : null
        ];
    }
}
