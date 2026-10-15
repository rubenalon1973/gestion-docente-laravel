<?php

namespace Database\Factories;

use App\Models\PricingUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingUnit>
 */
class PricingUnitFactory extends Factory
{
    public function definition(): array
    {
        $code = fake()->unique()->lexify('unit_????');

        return [
            'code' => $code,
            'name' => ucfirst(str_replace('_', ' ', $code)),
            'is_active' => true,
        ];
    }
}
