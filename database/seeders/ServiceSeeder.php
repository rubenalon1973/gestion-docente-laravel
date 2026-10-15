<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['code' => 'training_delivery', 'name' => 'Impartición de formación', 'description' => 'Preparación e impartición de una acción formativa.'],
            ['code' => 'tutoring', 'name' => 'Tutoría', 'description' => 'Acompañamiento y seguimiento individual o grupal.'],
            ['code' => 'content_creation', 'name' => 'Creación de contenidos', 'description' => 'Diseño y elaboración de contenidos formativos.'],
            ['code' => 'content_update', 'name' => 'Actualización de contenidos', 'description' => 'Revisión y puesta al día de contenidos existentes.'],
        ])->each(fn (array $service) => Service::query()->firstOrCreate(
            ['code' => $service['code']],
            [...$service, 'is_active' => true],
        ));
    }
}
