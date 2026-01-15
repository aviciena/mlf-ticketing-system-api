<?php

namespace App\Http\Resources;

use App\Helpers\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainEventSubEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Utils::encode($this->id),
            'name' => $this->title,
        ];
    }
}
