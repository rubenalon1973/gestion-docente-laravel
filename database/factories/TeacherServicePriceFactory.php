<?php

namespace Database\Factories;

use App\Models\PricingUnit;
use App\Models\Service;
use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherServicePrice>
 */
class TeacherServicePriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'service_id' => Service::factory(),
            'subject_id' => null,
            'pricing_unit_id' => PricingUnit::factory(),
            'modality' => fake()->randomElement(['in_person', 'online', 'all']),
            'net_amount' => fake()->randomFloat(2, 0, 1000),
            'currency' => 'EUR',
            'valid_from' => fake()->date(),
            'valid_until' => null,
        ];
    }
}
