<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'code' => Str::slug($name),
            'name' => ucfirst($name),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
