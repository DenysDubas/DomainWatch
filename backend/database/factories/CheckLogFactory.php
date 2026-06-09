<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CheckStatus;
use App\Models\CheckLog;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CheckLog> */
class CheckLogFactory extends Factory
{
    protected $model = CheckLog::class;

    public function definition(): array
    {
        return [
            'domain_id'     => Domain::factory(),
            'status'        => CheckStatus::Up,
            'response_code' => 200,
            'response_time' => fake()->randomFloat(2, 50, 500),
            'error_message' => null,
            'checked_at'    => now(),
        ];
    }
}
