<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CheckStatus;
use App\Models\Domain;
use App\Models\User;
use App\Services\PingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_ping_is_recorded(): void
    {
        $url = 'https://93.184.216.34';
        Http::fake([$url => Http::response('OK', 200)]);

        $domain = Domain::factory()->create([
            'user_id' => User::factory(),
            'url'     => $url,
        ]);

        $result = app(PingService::class)->execute($domain);

        $this->assertSame(CheckStatus::Up, $result->status);
        $this->assertSame(200, $result->responseCode);
        $this->assertDatabaseHas('check_logs', [
            'domain_id' => $domain->id,
            'status'    => 'up',
        ]);
        $this->assertSame(CheckStatus::Up, $domain->fresh()->last_status);
    }

    public function test_failed_ping_records_error(): void
    {
        $url = 'https://93.184.216.34';
        Http::fake([$url => Http::response('Error', 500)]);

        $domain = Domain::factory()->create([
            'user_id' => User::factory(),
            'url'     => $url,
        ]);

        $result = app(PingService::class)->execute($domain);

        $this->assertSame(CheckStatus::Down, $result->status);
        $this->assertSame(500, $result->responseCode);
        $this->assertNotNull($result->errorMessage);
    }

    public function test_blocked_url_is_marked_down_without_http_call(): void
    {
        Http::fake();

        $domain = Domain::factory()->create([
            'user_id' => User::factory(),
            'url'     => 'http://127.0.0.1',
        ]);

        $result = app(PingService::class)->execute($domain);

        $this->assertSame(CheckStatus::Down, $result->status);
        Http::assertNothingSent();
    }
}
