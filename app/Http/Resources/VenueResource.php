<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
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
            'category' => $this->category,
            'description' => $this->description,
            'street' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'province' => $this->province,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'maps_embed' => $this->maps_embed,
            'maps' => $this->maps,
            'is_disable_delete' => count($this->events) > 0 ? true : false,
        ];
    }
}
