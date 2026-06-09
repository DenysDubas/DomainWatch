<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\CheckLogRepo;
use Illuminate\Console\Command;

class PruneCheckLogs extends Command
{
    protected $signature = 'check-logs:prune {--days=90 : Delete logs older than this many days}';

    protected $description = 'Remove old domain check logs to control table growth';

    public function __construct(private readonly CheckLogRepo $repo)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $deleted = $this->repo->pruneOlderThan($days);

        $this->info("Pruned {$deleted} check log(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
