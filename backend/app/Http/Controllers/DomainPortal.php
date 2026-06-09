<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\DomainPayload;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use App\Services\DomainService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DomainPortal extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly DomainService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Domain::class);

        $domains = $this->service->listForUser($request->user()->id);

        return DomainResource::collection($domains);
    }

    public function store(StoreDomainRequest $request): JsonResponse
    {
        $this->authorize('create', Domain::class);

        $domain = $this->service->create(
            $request->user(),
            DomainPayload::fromArray($request->validated()),
        );

        return (new DomainResource($domain))->response()->setStatusCode(201);
    }

    public function show(Domain $domain): DomainResource
    {
        $this->authorize('view', $domain);

        return new DomainResource($domain);
    }

    public function update(UpdateDomainRequest $request, Domain $domain): DomainResource
    {
        $this->authorize('update', $domain);

        $updated = $this->service->update($domain, DomainPayload::fromArray($request->validated()));

        return new DomainResource($updated);
    }

    public function destroy(Domain $domain): JsonResponse
    {
        $this->authorize('delete', $domain);

        $this->service->delete($domain);

        return response()->json(null, 204);
    }
}
