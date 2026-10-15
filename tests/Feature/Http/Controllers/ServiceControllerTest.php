<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Service;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class ServiceControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_lists_services_and_opens_creation_form(): void
    {
        Service::factory()->create(['name' => 'Tutoría']);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSeeText('Tutoría')
            ->assertSeeText('Añadir servicio');

        $this->get(route('services.create'))
            ->assertOk()
            ->assertSeeText('El código interno se generará automáticamente');
    }

    public function test_creates_service_with_generated_stable_code(): void
    {
        $response = $this->post(route('services.store'), [
            'code' => 'attempted_override',
            'name' => 'Diseño de evaluación',
            'description' => 'Creación de instrumentos de evaluación.',
        ]);

        $response->assertRedirectToRoute('services.index');
        $this->assertDatabaseHas('services', [
            'code' => 'diseno_de_evaluacion',
            'name' => 'Diseño de evaluación',
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('services', ['code' => 'attempted_override']);
    }

    public function test_updates_visible_fields_without_changing_code(): void
    {
        $service = Service::factory()->create(['code' => 'stable_code']);

        $this->put(route('services.update', $service), [
            'code' => 'changed_code',
            'name' => 'Nombre actualizado',
            'description' => 'Descripción actualizada.',
        ])->assertRedirectToRoute('services.index');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'code' => 'stable_code',
            'name' => 'Nombre actualizado',
            'description' => 'Descripción actualizada.',
        ]);
    }

    public function test_activates_and_deactivates_service_without_deleting_it(): void
    {
        $service = Service::factory()->create(['is_active' => true]);

        $this->patch(route('services.update-status', $service))
            ->assertRedirectToRoute('services.index');
        $this->assertModelExists($service);
        $this->assertFalse($service->fresh()->is_active);

        $this->patch(route('services.update-status', $service))
            ->assertRedirectToRoute('services.index');
        $this->assertTrue($service->fresh()->is_active);
    }

    public function test_validates_service_fields_in_spanish(): void
    {
        Service::factory()->create(['name' => 'Tutoría']);

        $this->post(route('services.store'), ['name' => 'Tutoría'])
            ->assertSessionHasErrors(['name' => 'Ya existe un servicio con ese nombre.']);
    }
}
