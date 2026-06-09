<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'url'                => $this->url,
            'name'               => $this->name,
            'check_interval'     => $this->check_interval,
            'timeout'            => $this->timeout,
            'method'             => $this->method->value,
            'is_active'          => $this->is_active,
            'last_checked_at'    => $this->last_checked_at?->toIso8601String(),
            'last_status'        => $this->last_status?->value,
            'last_response_code' => $this->last_response_code,
            'created_at'         => $this->created_at->toIso8601String(),
            'updated_at'         => $this->updated_at->toIso8601String(),
        ];
    }
}
