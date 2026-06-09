<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\CheckStatus;
use App\Models\Domain;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Domain $domain,
        public readonly CheckStatus $status,
    ) {}
}
