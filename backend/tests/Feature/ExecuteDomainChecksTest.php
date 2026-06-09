<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\DomainPingJob;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExecuteDomainChecksTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_jobs_for_due_domains(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $due = Domain::factory()->create([
            'user_id'         => $user->id,
            'url'             => 'https://example.com/due',
            'last_checked_at' => now()->subMinutes(10),
            'check_interval'  => 5,
            'is_active'       => true,
        ]);
        Domain::factory()->inactive()->create([
            'user_id' => $user->id,
            'url'     => 'https://example.com/inactive',
        ]);
        Domain::factory()->create([
            'user_id'         => $user->id,
            'url'             => 'https://example.com/recent',
            'last_checked_at' => now()->subMinute(),
            'check_interval'  => 5,
            'is_active'       => true,
        ]);

        $this->artisan('domains:check')->assertSuccessful();

        Queue::assertPushed(DomainPingJob::class, fn (DomainPingJob $job) => $job->domain->id === $due->id);
        Queue::assertPushed(DomainPingJob::class, 1);
    }
}
