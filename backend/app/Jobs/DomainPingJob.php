<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Domain;
use App\Services\PingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class DomainPingJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public readonly Domain $domain) {}

    public function uniqueId(): string
    {
        return (string) $this->domain->id;
    }

    public function uniqueFor(): int
    {
        return max(60, $this->domain->check_interval * 60);
    }

    public function timeout(): int
    {
        return min(120, $this->domain->timeout + 15);
    }

    public function handle(PingService $pingService): void
    {
        $this->domain->refresh();
        $pingService->execute($this->domain);
    }

    public function failed(Throwable $e): void
    {
        $this->domain->refresh();
        $this->domain->forceFill(['checking_started_at' => null])->save();

        \Illuminate\Support\Facades\Log::error(
            "DomainPingJob failed for domain #{$this->domain->id}: {$e->getMessage()}",
        );
    }
}
