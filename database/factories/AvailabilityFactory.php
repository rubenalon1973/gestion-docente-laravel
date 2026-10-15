<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 19);
        $duration = fake()->randomElement([1, 2, 3]);
        $endHour = $startHour + $duration;

        return [
            'teacher_id' => Teacher::factory(),
            'day_of_week' => fake()->numberBetween(1, 7),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $endHour),
            'modality' => fake()->randomElement([
                'online',
                'in_person',
            ]),
        ];
    }
}
