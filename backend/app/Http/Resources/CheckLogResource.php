<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'domain_id'     => $this->domain_id,
            'status'        => $this->status->value,
            'response_code' => $this->response_code,
            'response_time' => $this->response_time,
            'error_message' => $this->error_message,
            'checked_at'    => $this->checked_at->toIso8601String(),
        ];
    }
}
