<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherTool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherTool>
 */
class TeacherToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement([
            'software',
            'ai',
        ]);

        $toolName = $category === 'ai'
            ? fake()->randomElement([
                'ChatGPT',
                'Microsoft Copilot',
                'Gemini',
                'Generador de imágenes',
                'Herramienta de transcripción',
            ])
            : fake()->randomElement([
                'Microsoft Word',
                'Microsoft Excel',
                'PowerPoint',
                'Moodle',
                'LibreOffice',
            ]);

        return [
            'teacher_id' => Teacher::factory(),
            'category' => $category,
            'tool_name' => $toolName,
            'level' => fake()->randomElement([
                'básico',
                'intermedio',
                'avanzado',
            ]),
        ];
    }
}
