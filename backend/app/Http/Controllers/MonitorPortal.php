<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\CheckLogResource;
use App\Jobs\DomainPingJob;
use App\Models\Domain;
use App\Repositories\CheckLogRepo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MonitorPortal extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CheckLogRepo $logRepo) {}

    public function index(Domain $domain): AnonymousResourceCollection
    {
        $this->authorize('view', $domain);

        $logs = $this->logRepo->paginateForDomain($domain->id, 50);

        return CheckLogResource::collection($logs);
    }

    public function triggerCheck(Domain $domain): JsonResponse
    {
        $this->authorize('check', $domain);

        DomainPingJob::dispatch($domain);

        return response()->json(['message' => 'Domain check queued.'], 202);
    }
}
