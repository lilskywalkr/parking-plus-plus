<?php

namespace Database\Factories;

use App\Enums\ParkingRecordActionEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Record>
 */
class RecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user_id = fake()->numberBetween(1, 2);

        return [
            'user_id' => $user_id,
            'parking_space_id' => fake()->numberBetween(1, 10),
            'registration_plates' => ($user_id === 1 ? fake()->word() : null),
            'action' => (
                $user_id === 1 ? (
                    fake()->randomElement([ParkingRecordActionEnum::DRIVE_IN, ParkingRecordActionEnum::DRIVE_OUT])
                ) : (
                    fake()->randomElement([ParkingRecordActionEnum::BLOCKED, ParkingRecordActionEnum::UNBLOCKED])
                )
            ),
        ];
    }
}
