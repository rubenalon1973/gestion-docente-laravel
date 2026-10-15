<?php

namespace Database\Factories;

use App\Models\Qualification;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Qualification>
 */
class QualificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'title' => fake()->randomElement([
                'Grado en Educación',
                'Grado en Matemáticas',
                'Grado en Filología',
                'Grado en Ciencias',
                'Máster en Formación del Profesorado',
                'Certificado de docencia',
                'Curso de formación especializada',
            ]),
            'institution' => fake()->randomElement([
                'Centro Formativo Ejemplo',
                'Instituto Ficticio Norte',
                'Universidad de Demostración',
                null,
            ]),
            'completion_year' => fake()->numberBetween(1995, 2026),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }
}
