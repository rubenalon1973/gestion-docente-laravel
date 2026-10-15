<?php

namespace Database\Seeders;

use App\Models\PricingUnit;
use Illuminate\Database\Seeder;

class PricingUnitSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['code' => 'hour', 'name' => 'Hora'],
            ['code' => 'course', 'name' => 'Curso'],
            ['code' => 'day', 'name' => 'Jornada'],
        ])->each(fn (array $unit) => PricingUnit::query()->updateOrCreate(
            ['code' => $unit['code']],
            ['name' => $unit['name'], 'is_active' => true]
        ));
    }
}
