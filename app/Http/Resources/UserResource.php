<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->hashid,
            'name' => $this->name,
            'username' => $this->username,
            'is_admin' => $this->is_admin == 1 ? true : false,
            'event_id' => $this->event_id ?? "0",
            'event_name' => $this->events ?  $this->events->title : "-",
            'role' => $this->role ? $this->role->code : null,
            'role_description' => $this->role ? $this->role->description : null
        ];
    }
}
