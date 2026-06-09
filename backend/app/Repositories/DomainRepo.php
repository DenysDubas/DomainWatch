<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DomainRepo
{
    public function allForUser(int $userId): Collection
    {
        return Domain::where('user_id', $userId)->latest()->get();
    }

    public function findForUser(int $id, int $userId): ?Domain
    {
        return Domain::where('id', $id)->where('user_id', $userId)->first();
    }

    public function createForUser(User $user, array $data): Domain
    {
        return $user->domains()->create($data);
    }

    public function update(Domain $domain, array $data): Domain
    {
        $domain->update($data);

        return $domain->refresh();
    }

    public function delete(Domain $domain): void
    {
        $domain->delete();
    }

    public function getDueForChecking(): Collection
    {
        $driver = DB::connection()->getDriverName();

        return Domain::where('is_active', true)
            ->where(function ($query) use ($driver) {
                $query->whereNull('last_checked_at')
                    ->orWhere(function ($q) use ($driver) {
                        match ($driver) {
                            'mysql'  => $q->whereRaw('last_checked_at <= DATE_SUB(NOW(), INTERVAL check_interval MINUTE)'),
                            'pgsql'  => $q->whereRaw("last_checked_at <= NOW() - (check_interval * INTERVAL '1 minute')"),
                            'sqlite' => $q->whereRaw("last_checked_at <= datetime('now', '-' || check_interval || ' minutes')"),
                            default  => $q->where('last_checked_at', '<=', now()->subMinute()),
                        };
                    });
            })
            ->where(function ($query) {
                $query->whereNull('checking_started_at')
                    ->orWhere('checking_started_at', '<', now()->subMinutes(10));
            })
            ->orderBy('last_checked_at')
            ->get();
    }
}
