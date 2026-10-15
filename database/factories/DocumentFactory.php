<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement([
            'cv',
            'photo',
            'qualification',
            'other',
        ]);

        $fileType = match ($category) {
            'photo' => [
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
            ],
            default => [
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
            ],
        };

        $identifier = fake()->uuid();

        return [
            'teacher_id' => Teacher::factory(),
            'category' => $category,
            'original_name' => "documento_ficticio.{$fileType['extension']}",
            'disk' => 'local',
            'storage_path' => "demo/teachers/{$identifier}.{$fileType['extension']}",
            'mime_type' => $fileType['mime_type'],
            'size_bytes' => fake()->numberBetween(20_000, 1_800_000),
            'sha256' => fake()->sha256(),
        ];
    }
}
