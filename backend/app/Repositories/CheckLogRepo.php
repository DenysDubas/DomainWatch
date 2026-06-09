<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CheckLog;
use App\Models\Domain;
use Illuminate\Pagination\LengthAwarePaginator;

class CheckLogRepo
{
    public function paginateForDomain(int $domainId, int $perPage = 20): LengthAwarePaginator
    {
        return CheckLog::where('domain_id', $domainId)
            ->latest('checked_at')
            ->paginate($perPage);
    }

    public function createForDomain(Domain $domain, array $data): CheckLog
    {
        return $domain->checkLogs()->create($data);
    }

    public function uptimePercentage(int $domainId, int $days = 7): float
    {
        $total = CheckLog::where('domain_id', $domainId)
            ->where('checked_at', '>=', now()->subDays($days))
            ->count();

        if ($total === 0) {
            return 100.0;
        }

        $up = CheckLog::where('domain_id', $domainId)
            ->where('status', 'up')
            ->where('checked_at', '>=', now()->subDays($days))
            ->count();

        return round(($up / $total) * 100, 2);
    }

    public function pruneOlderThan(int $days): int
    {
        return CheckLog::where('checked_at', '<', now()->subDays($days))->delete();
    }
}
