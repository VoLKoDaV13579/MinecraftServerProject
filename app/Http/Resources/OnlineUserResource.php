<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OnlineUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'username' => $this->username,
            'realname' => $this->realname,
            'world' => $this->world,
            'coordinates' => [
                'x' => round($this->x, 2),
                'y' => round($this->y, 2),
                'z' => round($this->z, 2),
                'yaw' => round($this->yaw, 2),
                'pitch' => round($this->pitch, 2),
            ],
            'login_time' => $this->getFormattedLastLogin(),
        ];
    }
}
