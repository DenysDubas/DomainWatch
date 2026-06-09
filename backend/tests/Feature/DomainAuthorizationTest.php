<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DomainAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_domain(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/v1/domains/{$domain->id}")->assertNotFound();
        $this->putJson("/api/v1/domains/{$domain->id}", [
            'url'            => 'https://example.com/hacked',
            'name'           => 'Hacked',
            'check_interval' => 5,
            'timeout'        => 10,
            'method'         => 'GET',
        ])->assertNotFound();
        $this->deleteJson("/api/v1/domains/{$domain->id}")->assertNotFound();
        $this->getJson("/api/v1/domains/{$domain->id}/logs")->assertNotFound();
        $this->postJson("/api/v1/domains/{$domain->id}/check")->assertNotFound();
    }
}
