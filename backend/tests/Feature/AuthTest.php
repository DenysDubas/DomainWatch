<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_can_login_and_receives_single_token(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token']);

        $this->assertEquals(1, $user->tokens()->count());

        $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertOk();

        $this->assertEquals(1, $user->fresh()->tokens()->count());
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJson(['email' => $user->email]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }
}
