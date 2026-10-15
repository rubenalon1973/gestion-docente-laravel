<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Availability;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class TeacherAvailabilityControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_adds_valid_availability_to_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.availabilities.store', $teacher), [
            'days' => [2],
            'start_time' => '09:00',
            'end_time' => '11:00',
            'modality' => 'online',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Disponibilidad añadida correctamente.');
        $this->assertDatabaseHas('availabilities', [
            'teacher_id' => $teacher->id,
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'modality' => 'online',
        ]);
    }

    public function test_adds_same_availability_to_multiple_selected_days(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.availabilities.store', $teacher), [
            'days' => [1, 3, 5],
            'start_time' => '16:00',
            'end_time' => '18:00',
            'modality' => 'in_person',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Disponibilidad añadida correctamente.');
        $this->assertDatabaseCount('availabilities', 3);

        foreach ([1, 3, 5] as $day) {
            $this->assertDatabaseHas('availabilities', [
                'teacher_id' => $teacher->id,
                'day_of_week' => $day,
                'start_time' => '16:00',
                'end_time' => '18:00',
                'modality' => 'in_person',
            ]);
        }
    }

    public function test_rejects_invalid_availability(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.availabilities.store', $teacher), [
            'days' => [],
            'start_time' => '11:00',
            'end_time' => '10:00',
            'modality' => 'hybrid',
        ]);

        $response->assertSessionHasErrors([
            'days' => 'Debes seleccionar al menos un día de la semana.',
            'end_time' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'modality' => 'La modalidad seleccionada no es válida.',
        ]);
        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_renders_edit_form_for_teacher_availability(): void
    {
        $teacher = Teacher::factory()->create();
        $availability = Availability::factory()->for($teacher)->create([
            'start_time' => '09:00',
        ]);

        $response = $this->get(
            route('teachers.availabilities.edit', [$teacher, $availability]),
        );

        $response->assertOk()
            ->assertSee('value="09:00"', false);
    }

    public function test_updates_teacher_availability(): void
    {
        $teacher = Teacher::factory()->create();
        $availability = Availability::factory()->for($teacher)->create();

        $response = $this->put(
            route('teachers.availabilities.update', [$teacher, $availability]),
            [
                'day_of_week' => 5,
                'start_time' => '16:00',
                'end_time' => '18:00',
                'modality' => 'in_person',
            ],
        );

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Disponibilidad actualizada correctamente.');
        $this->assertDatabaseHas('availabilities', [
            'id' => $availability->id,
            'day_of_week' => 5,
            'start_time' => '16:00',
            'end_time' => '18:00',
            'modality' => 'in_person',
        ]);
    }

    public function test_returns_404_for_availability_from_another_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $availability = Availability::factory()->for($otherTeacher)->create();

        $response = $this->delete(
            route('teachers.availabilities.destroy', [$teacher, $availability]),
        );

        $response->assertNotFound();
        $this->assertModelExists($availability);
    }

    public function test_deletes_teacher_availability(): void
    {
        $teacher = Teacher::factory()->create();
        $availability = Availability::factory()->for($teacher)->create();

        $response = $this->delete(
            route('teachers.availabilities.destroy', [$teacher, $availability]),
        );

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Disponibilidad eliminada correctamente.');
        $this->assertModelMissing($availability);
    }
}
