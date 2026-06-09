<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HttpMethod;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Domain> */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'url'            => 'https://example.com/' . fake()->unique()->uuid(),
            'name'           => fake()->words(2, true),
            'check_interval' => 5,
            'timeout'        => 10,
            'method'         => HttpMethod::Get,
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
