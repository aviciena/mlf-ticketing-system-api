<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $is_expired = false;

        if ($now->lessThan($start)) {
            $status = [
                'code' => 'not_started',
                'description' => 'Not Started',
            ];
        } elseif ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
            $status = [
                'code' => 'active',
                'description' => 'Active',
            ];
        } else {
            $status = [
                'code' => 'expired',
                'description' => 'Expired',
            ];
            $is_expired = true;
        }

        return [
            'id' => $this->id,
            'event' => [
                'id' => $this->event->id ?? null,
                'title' => $this->event->title ?? null,
            ],
            'event_name' => $this->event->title ?? null,
            'category' => [
                'code' => $this->category->code ?? null,
                'description' => $this->category->description ?? null,
            ],
            'event_ticket_category_str' => $this->category->description ?? null,
            'event_ticket_category' => $this->category->code ?? null,
            'title' => $this->title,
            'start_date' => Carbon::parse($this->start_date)->format('d/m/y H:i'),
            'end_date' => Carbon::parse($this->end_date)->format('d/m/y H:i'),
            'sale_start_date' => Carbon::parse($this->sale_start_date)->format('d/m/y H:i'),
            'sale_end_date' => Carbon::parse($this->sale_end_date)->format('d/m/y H:i'),
            'min_quantity' => $this->min_quantity,
            'max_quantity' => $this->max_quantity,
            'quota' => $this->quota,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'price_after_discount' => $this->price_after_discount,
            'allow_multiple_checkin' => $this->allow_multiple_checkin == 1 ? true : false,
            'allow_multiple_checkin_str' => $this->allow_multiple_checkin == 1 ? 'Yes' : 'No',
            'auto_checkout' => $this->auto_checkout == 1 ? true : false,
            'auto_checkout_str' => $this->auto_checkout == 1 ? 'Yes' : 'No',
            'validity_type' => $this->validityType->code ?? null,
            'validity_type_str' => $this->validityType->description ?? null,
            'status' => $status,
            'auto_checkout' => $this->auto_checkout == 1 ? true : false,
            'external_event_ticket_id' => $this->external_event_ticket_id,
            'is_expired' => $is_expired,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'description' => $this->description
        ];
    }
}
