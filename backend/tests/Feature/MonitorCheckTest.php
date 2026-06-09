<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\DomainPingJob;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MonitorCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_check_is_queued(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/domains/{$domain->id}/check")
            ->assertAccepted()
            ->assertJson(['message' => 'Domain check queued.']);

        Queue::assertPushed(DomainPingJob::class);
    }
}
