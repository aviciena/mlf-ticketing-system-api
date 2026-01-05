<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTicketsOptionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'validity_type' => $this->validityType->description,
            'validity_start_date' => Carbon::parse($this->start_date)->format('d/m/Y H:i'),
            'validity_end_date' => Carbon::parse($this->end_date)->format('d/m/Y H:i')
        ];
    }
}
