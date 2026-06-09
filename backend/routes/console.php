<?php

declare(strict_types=1);

use App\Console\Commands\ExecuteDomainChecks;
use App\Console\Commands\PruneCheckLogs;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ExecuteDomainChecks::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command(PruneCheckLogs::class)
    ->daily()
    ->at('03:00');
