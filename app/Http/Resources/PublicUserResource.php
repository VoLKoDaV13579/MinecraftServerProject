<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'username' => $this->username,
            'realname' => $this->realname,
            'world' => $this->world,
            'is_online' => $this->isOnline(),
            'registered_at' => $this->getFormattedRegDate(),
            'last_login_at' => $this->when($this->lastlogin > 0, $this->getFormattedLastLogin()),
        ];
    }
}
