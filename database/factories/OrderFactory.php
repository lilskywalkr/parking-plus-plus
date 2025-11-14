<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_price' => fake()->numberBetween(6, 7.2),
            'status' => fake()->randomElement([OrderStatusEnum::OPEN, OrderStatusEnum::COMPLETE]),
            'user_id' => fake()->randomNumber(),
            'registration_plates' => fake()->country(),
            'session_id' => str_repeat(chr(65 + rand(0, 25)), 65)
        ];
    }
}
