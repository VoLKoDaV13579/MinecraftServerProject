<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'server' => [
                'total_users' => $this->resource['total_users'] ?? 0,
                'online_users' => $this->resource['online_users'] ?? 0,
                'registered_today' => $this->resource['registered_today'] ?? 0,
                'registered_this_week' => $this->resource['registered_this_week'] ?? 0,
                'registered_this_month' => $this->resource['registered_this_month'] ?? 0,
            ],
            'worlds' => $this->when(
                isset($this->resource['worlds']),
                $this->resource['worlds'] ?? 0
            ),
            'registration_chart' => $this->when(
                isset($this->resource['registration_chart']),
                $this->resource['registration_chart'] ?? 0
            ),
        ];
    }
}
