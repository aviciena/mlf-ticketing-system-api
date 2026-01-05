<?php

namespace App\Http\Resources;

use App\Helpers\Utils;
use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubEventResource extends JsonResource
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

        return [
            'id' => $this->id,
            'venue_id' => $this->venue->id ?? null,
            'venue_name' => $this->venue->title ?? null,
            'status' => [
                'code' => $this->status->code ?? null,
                'description' => $this->status->description ?? null,
            ],
            'event_name' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'date_str' => Utils::formatRange($this->start_date, $this->end_date),
            'description' => $this->description,
            'main_event_name' => $this->parentEvent->title
        ];
    }
}
