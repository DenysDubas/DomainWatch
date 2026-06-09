<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\DomainPingJob;
use App\Repositories\DomainRepo;
use Illuminate\Console\Command;

class ExecuteDomainChecks extends Command
{
    protected $signature   = 'domains:check';
    protected $description = 'Dispatch ping jobs for all active domains that are due for checking';

    public function __construct(private readonly DomainRepo $repo)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $domains = $this->repo->getDueForChecking();

        foreach ($domains as $domain) {
            DomainPingJob::dispatch($domain);
        }

        $this->info("Dispatched {$domains->count()} ping job(s).");

        return self::SUCCESS;
    }
}
