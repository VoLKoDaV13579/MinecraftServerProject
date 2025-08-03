<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'realname' => $this->realname,
            'email' => $this->when($this->shouldShowEmail($request), $this->email),
            'world' => $this->world,
            'coordinates' => $this->getCoordinates(),
            'is_online' => $this->isOnline(),
            'registered_at' => $this->getFormattedRegDate(),
            'last_login_at' => $this->getFormattedLastLogin(),
            'registration_ip' => $this->when($this->shouldShowIp($request), $this->regip),
            'last_ip' => $this->when($this->shouldShowIp($request), $this->ip),
        ];
    }

    private function shouldShowEmail($request): bool
    {
        $user = $request->user();
        return $user && $user->id === $this->id;
    }

    private function shouldShowIp($request): bool
    {
        $user = $request->user();
        return $user && $user->id === $this->id;
    }
}
