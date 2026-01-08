<?php

namespace App\Http\Resources;

use App\Helpers\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainEventTicketResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'end_date' => Utils::getDateFormat($this->sale_end_date),
            'end_time' => Utils::getHourFormat($this->sale_end_date),
            'price' => $this->price,
            'is_available' => $this->quota > 0,
            'is_display' => Utils::isDateRange($this->sale_start_date, $this->sale_end_date),
            'is_expired' => Utils::isExpired($this->end_date),
            'min' => $this->min_quantity,
            'max' => $this->max_quantity,
            'count' => $this->quota,
            'is_primary' => true,
        ];
    }
}
