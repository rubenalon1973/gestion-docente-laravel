<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            'Matemáticas',
            'Lengua castellana',
            'Inglés',
            'Física',
            'Química',
            'Biología',
            'Historia',
            'Geografía',
            'Informática',
            'Economía',
            'Administración',
            'Prevención de riesgos laborales',
        ];

        $name = fake()->unique()->randomElement($subjects);

        return [
            'name' => $name,
            'description' => "Materia ficticia de demostración: {$name}.",
        ];
    }
}
