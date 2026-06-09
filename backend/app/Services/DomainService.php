<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\DomainPayload;
use App\Models\Domain;
use App\Models\User;
use App\Repositories\DomainRepo;
use Illuminate\Database\Eloquent\Collection;

class DomainService
{
    public function __construct(private readonly DomainRepo $repo) {}

    public function listForUser(int $userId): Collection
    {
        return $this->repo->allForUser($userId);
    }

    public function findForUser(int $id, int $userId): ?Domain
    {
        return $this->repo->findForUser($id, $userId);
    }

    public function create(User $user, DomainPayload $payload): Domain
    {
        return $this->repo->createForUser($user, [
            'url'            => $payload->url,
            'name'           => $payload->name,
            'check_interval' => $payload->checkInterval,
            'timeout'        => $payload->timeout,
            'method'         => $payload->method,
            'is_active'      => $payload->isActive,
        ]);
    }

    public function update(Domain $domain, DomainPayload $payload): Domain
    {
        return $this->repo->update($domain, [
            'url'            => $payload->url,
            'name'           => $payload->name,
            'check_interval' => $payload->checkInterval,
            'timeout'        => $payload->timeout,
            'method'         => $payload->method,
            'is_active'      => $payload->isActive,
        ]);
    }

    public function delete(Domain $domain): void
    {
        $this->repo->delete($domain);
    }
}
