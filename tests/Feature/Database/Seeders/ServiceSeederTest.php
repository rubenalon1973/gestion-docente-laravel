<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\Service;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ServiceSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_is_idempotent_and_preserves_later_edits(): void
    {
        $this->seed(ServiceSeeder::class);

        $service = Service::query()->where('code', 'tutoring')->firstOrFail();
        $service->update([
            'name' => 'Tutoría personalizada',
            'description' => 'Descripción administrada.',
            'is_active' => false,
        ]);

        $this->seed(ServiceSeeder::class);

        $this->assertDatabaseCount('services', 4);
        $this->assertDatabaseHas('services', [
            'code' => 'training_delivery',
            'name' => 'Impartición de formación',
        ]);
        $this->assertDatabaseHas('services', [
            'code' => 'tutoring',
            'name' => 'Tutoría personalizada',
            'description' => 'Descripción administrada.',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('services', ['code' => 'content_creation']);
        $this->assertDatabaseHas('services', ['code' => 'content_update']);
    }
}
