<?php

namespace App\Http\Resources;

use App\Helpers\Utils;
use Carbon\Carbon;
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
        $start = Carbon::parse($this->start_date)->locale('id');
        $end = Carbon::parse($this->end_date)->locale('id');

        if ($start->isSameDay($end)) {
            $eventDate = 'Digunakan pada hari ' . $start->translatedFormat('l, d F Y');
            $eventHour = $start->format('H.i') . ' - ' . $end->format('H.i') . ' WIB';
        } else {
            if ($start->year === $end->year) {
                $eventDate = 'Berlaku mulai ' . $start->translatedFormat('l, d F') . ' s.d ' . $end->translatedFormat('l, d F Y');
            } else {
                $eventDate = 'Berlaku mulai ' . $start->translatedFormat('l, d F Y') . ' s.d ' . $end->translatedFormat('l, d F Y');
            }

            $eventHour = $start->format('H.i') . ' - ' . $end->format('H.i') . ' WIB';
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'end_date' => $this->sale_end_date ? Utils::getDateFormat($this->sale_end_date) : '',
            'end_time' => $this->sale_end_date ? Utils::getHourFormat($this->sale_end_date) : '',
            'event_date' => $eventDate,
            'event_hour' => $eventHour,
            'price' => $this->price ?? 0,
            'is_available' => $this->quota > 0,
            'is_display' => $this->sale_start_date && $this->sale_end_date ? Utils::isDateRange($this->sale_start_date, $this->sale_end_date) : true,
            'is_expired' => Utils::isExpired($this->end_date),
            'min' => $this->min_quantity,
            'max' => $this->max_quantity,
            'count' => $this->quota,
            'is_primary' => true,
        ];
    }
}
