<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_names' => fake()->lastName().' '.fake()->lastName(),
            'identity_document' => 'TEST'.fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.9)->phoneNumber(),
            'birth_date' => fake()
                ->dateTimeBetween('-65 years', '-22 years')
                ->format('Y-m-d'),
            'birth_place' => fake()->city(),
            'locality' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'province' => fake()->state(),
            'country' => fake()->country(),
            'address' => fake()->optional(0.9)->streetAddress(),
            'years_of_experience' => fake()->numberBetween(0, 40),
            'creates_content' => fake()->randomElement([true, false, null]),
            'updates_content' => fake()->randomElement([true, false, null]),
            'status' => 'active',
            'internal_notes' => fake()->optional()->sentence(),
        ];
    }
}
