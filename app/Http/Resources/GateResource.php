<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashid,
            'code' => $this->code,
            'description' => $this->description,
            'name' => $this->description,
            'is_active' => $this->active == 1 ? true : false,
            'is_active_str' => $this->active == 1 ? 'Yes' : 'No',
            'is_used' => $this->checkInGateTickets()->exists(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
