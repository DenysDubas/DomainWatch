<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\CheckStatus;

final readonly class PingResultPayload
{
    public function __construct(
        public CheckStatus $status,
        public ?int        $responseCode,
        public float       $responseTime,
        public ?string     $errorMessage,
    ) {}

    public function isUp(): bool
    {
        return $this->status === CheckStatus::Up;
    }
}
