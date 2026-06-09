<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DomainCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_user_can_list_own_domains(): void
    {
        $user = $this->actingAsUser();
        Domain::factory()->count(2)->create(['user_id' => $user->id]);
        Domain::factory()->create();

        $this->getJson('/api/v1/domains')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_create_domain(): void
    {
        $this->actingAsUser();

        $payload = [
            'url'            => 'https://example.org',
            'name'           => 'Example',
            'check_interval' => 5,
            'timeout'        => 10,
            'method'         => 'GET',
            'is_active'      => true,
        ];

        $this->postJson('/api/v1/domains', $payload)
            ->assertCreated()
            ->assertJsonPath('data.url', 'https://example.org');

        $this->assertDatabaseHas('domains', ['url' => 'https://example.org']);
    }

    public function test_user_can_show_update_and_delete_domain(): void
    {
        $user = $this->actingAsUser();
        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'url'     => 'https://example.net/show',
        ]);

        $this->getJson("/api/v1/domains/{$domain->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $domain->id);

        $this->putJson("/api/v1/domains/{$domain->id}", [
            'url'            => 'https://example.net/updated',
            'name'           => 'Updated',
            'check_interval' => 10,
            'timeout'        => 15,
            'method'         => 'HEAD',
            'is_active'      => false,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated');

        $this->deleteJson("/api/v1/domains/{$domain->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
    }

    public function test_internal_url_is_rejected_on_create(): void
    {
        $this->actingAsUser();

        $this->postJson('/api/v1/domains', [
            'url'            => 'http://127.0.0.1',
            'name'           => 'Internal',
            'check_interval' => 5,
            'timeout'        => 10,
            'method'         => 'GET',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    }
}
