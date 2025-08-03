<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'server' => [
                'total_users' => $this->resource['total_users'],
                'online_users' => $this->resource['online_users'],
                'registered_today' => $this->resource['registered_today'],
                'registered_this_week' => $this->resource['registered_this_week'],
                'registered_this_month' => $this->resource['registered_this_month'],
            ],
            'worlds' => $this->when(
                isset($this->resource['worlds']),
                $this->resource['worlds']
            ),
            'registration_chart' => $this->when(
                isset($this->resource['registration_chart']),
                $this->resource['registration_chart']
            ),
        ];
    }
}
