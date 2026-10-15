<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\PricingUnit;
use App\Models\Service;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use App\Services\TeacherServicePriceService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class TeacherServicePriceControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_general_price(): void
    {
        [$teacher, $service, $unit] = $this->context();

        $response = $this->post(route('teachers.prices.store', $teacher), [
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'subject_id' => null,
            'modality' => 'all',
            'net_amount' => '85.50',
            'valid_from' => '2026-09-24',
            'valid_until' => null,
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Tarifa añadida correctamente.');
        $this->assertDatabaseHas('teacher_service_prices', [
            'teacher_id' => $teacher->id,
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'subject_id' => null,
            'modality' => 'all',
            'net_amount' => 85.50,
            'currency' => 'EUR',
            'valid_from' => '2026-09-24 00:00:00',
            'valid_until' => null,
        ]);
    }

    public function test_creates_specific_price_for_assigned_subject_and_modality(): void
    {
        [$teacher, $service, $unit] = $this->context();
        $subject = Subject::factory()->create();
        $teacher->subjects()->attach($subject);

        $response = $this->post(route('teachers.prices.store', $teacher), [
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'subject_id' => $subject->id,
            'modality' => 'online',
            'net_amount' => '120',
            'valid_from' => '2026-10-01',
            'valid_until' => '2026-12-31',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher);
        $this->assertDatabaseHas('teacher_service_prices', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'modality' => 'online',
            'valid_until' => '2026-12-31 00:00:00',
        ]);
    }

    /** @return array<string, array{string, string}> */
    public static function invalidAmounts(): array
    {
        return [
            'zero' => ['0', 'El importe debe ser mayor que cero.'],
            'three decimals' => ['12.345', 'El importe puede tener como máximo dos decimales.'],
        ];
    }

    #[DataProvider('invalidAmounts')]
    public function test_rejects_invalid_amount(string $amount, string $message): void
    {
        [$teacher, $service, $unit] = $this->context();

        $response = $this->post(route('teachers.prices.store', $teacher), [
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'modality' => 'all',
            'net_amount' => $amount,
            'valid_from' => '2026-09-24',
        ]);

        $response->assertSessionHasErrors(['net_amount' => $message]);
        $this->assertDatabaseCount('teacher_service_prices', 0);
    }

    public function test_rejects_inactive_catalog_options_and_unassigned_subject(): void
    {
        [$teacher, $service, $unit] = $this->context();
        $subject = Subject::factory()->create();
        $service->update(['is_active' => false]);
        $unit->update(['is_active' => false]);

        $response = $this->post(route('teachers.prices.store', $teacher), [
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'subject_id' => $subject->id,
            'modality' => 'online',
            'net_amount' => '100',
            'valid_from' => '2026-09-24',
        ]);

        $response->assertSessionHasErrors([
            'service_id' => 'El servicio seleccionado no está disponible.',
            'pricing_unit_id' => 'La unidad seleccionada no está disponible.',
            'subject_id' => 'La materia no está asignada al profesor.',
        ]);
        $this->assertDatabaseCount('teacher_service_prices', 0);
    }

    public function test_shows_service_management_notice_when_no_active_services_exist(): void
    {
        [$teacher, $service] = $this->context();
        $service->update(['is_active' => false]);

        $this->get(route('teachers.show', $teacher))
            ->assertOk()
            ->assertSeeText('No hay servicios activos.')
            ->assertSee(route('services.index'))
            ->assertDontSee('Añadir tarifa');
    }

    public function test_rejects_overlapping_price_with_spanish_message(): void
    {
        [$teacher, $service, $unit] = $this->context();
        $this->createPrice($teacher, $service, $unit, [
            'valid_from' => '2026-09-01',
            'valid_until' => '2026-09-30',
        ]);

        $response = $this->post(route('teachers.prices.store', $teacher), [
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'modality' => 'all',
            'net_amount' => '95',
            'valid_from' => '2026-09-30',
            'valid_until' => null,
        ]);

        $response->assertSessionHasErrors([
            'valid_from' => 'La vigencia se solapa con otra tarifa del mismo ámbito.',
        ]);
        $this->assertDatabaseCount('teacher_service_prices', 1);
    }

    public function test_shows_current_scheduled_and_historical_prices_with_scope_and_inactive_catalogs(): void
    {
        $this->travelTo('2026-09-24 12:00:00');
        [$teacher, $service, $unit] = $this->context();
        $service->update(['name' => 'Servicio vigente']);
        $this->createPrice($teacher, $service, $unit, [
            'valid_from' => '2026-09-01',
            'valid_until' => '2026-09-24 00:00:00',
        ]);

        $scheduledService = Service::factory()->create(['name' => 'Servicio programado']);
        $this->createPrice($teacher, $scheduledService, $unit, [
            'modality' => 'online',
            'valid_from' => '2026-09-25',
        ]);

        $historicalService = Service::factory()->create(['name' => 'Servicio histórico']);
        $historicalUnit = PricingUnit::factory()->create(['name' => 'Unidad histórica']);
        $this->createPrice($teacher, $historicalService, $historicalUnit, [
            'valid_from' => '2026-08-01',
            'valid_until' => '2026-09-23 00:00:00',
        ]);
        $historicalService->update(['is_active' => false]);
        $historicalUnit->update(['is_active' => false]);

        $response = $this->get(route('teachers.show', $teacher));

        $response->assertOk()
            ->assertSeeInOrder([
                'Vigentes',
                'Servicio vigente',
                'Programadas',
                'Servicio programado',
                'Históricas',
                'Servicio histórico',
            ])
            ->assertSeeText('General')
            ->assertSeeText('Específica')
            ->assertSeeText('Servicio inactivo')
            ->assertSeeText('Unidad inactiva');
    }

    public function test_closes_price_on_inclusive_last_day_without_deleting_it(): void
    {
        $this->travelTo('2026-09-24 12:00:00');
        [$teacher, $service, $unit] = $this->context();
        $price = $this->createPrice($teacher, $service, $unit, [
            'valid_from' => '2026-09-01',
        ]);

        $response = $this->patch(route('teachers.prices.close', [$teacher, $price]), [
            'valid_until' => '2026-09-24',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Vigencia de la tarifa cerrada correctamente.');
        $this->assertModelExists($price);
        $this->assertDatabaseHas('teacher_service_prices', [
            'id' => $price->id,
            'valid_until' => '2026-09-24 00:00:00',
        ]);

        $this->get(route('teachers.show', $teacher))
            ->assertSeeInOrder(['Vigentes', $service->name, 'Históricas']);
    }

    public function test_rejects_closing_before_start_or_when_price_is_already_historical(): void
    {
        $this->travelTo('2026-09-24 12:00:00');
        [$teacher, $service, $unit] = $this->context();
        $price = $this->createPrice($teacher, $service, $unit, [
            'valid_from' => '2026-09-10',
        ]);

        $this->patch(route('teachers.prices.close', [$teacher, $price]), [
            'valid_until' => '2026-09-09',
        ])->assertSessionHasErrors([
            'valid_until' => 'La última fecha de vigencia no puede ser anterior a la fecha de inicio.',
        ]);

        $price->update(['valid_until' => '2026-09-23']);

        $this->patch(route('teachers.prices.close', [$teacher, $price]), [
            'valid_until' => '2026-09-24',
        ])->assertSessionHasErrors([
            'valid_until' => 'No se puede cerrar una tarifa que ya es histórica.',
        ]);
        $this->assertDatabaseHas('teacher_service_prices', [
            'id' => $price->id,
            'valid_until' => '2026-09-23 00:00:00',
        ]);
    }

    public function test_returns_404_when_closing_another_teachers_price(): void
    {
        [$teacher] = $this->context();
        [$otherTeacher, $service, $unit] = $this->context();
        $price = $this->createPrice($otherTeacher, $service, $unit);

        $response = $this->patch(route('teachers.prices.close', [$teacher, $price]), [
            'valid_until' => '2026-09-24',
        ]);

        $response->assertNotFound();
        $this->assertNull($price->fresh()->valid_until);
    }

    /** @return array{Teacher, Service, PricingUnit} */
    private function context(): array
    {
        return [
            Teacher::factory()->create(),
            Service::factory()->create(),
            PricingUnit::factory()->create(),
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPrice(
        Teacher $teacher,
        Service $service,
        PricingUnit $unit,
        array $overrides = [],
    ): TeacherServicePrice {
        return app(TeacherServicePriceService::class)->create(array_replace([
            'teacher_id' => $teacher->id,
            'service_id' => $service->id,
            'subject_id' => null,
            'pricing_unit_id' => $unit->id,
            'modality' => 'all',
            'net_amount' => 90,
            'currency' => 'EUR',
            'valid_from' => '2026-09-01',
            'valid_until' => null,
        ], $overrides));
    }
}
