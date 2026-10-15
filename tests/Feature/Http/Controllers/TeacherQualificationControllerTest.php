<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Qualification;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class TeacherQualificationControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_adds_valid_qualification_to_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.qualifications.store', $teacher), [
            'teacher_id' => $otherTeacher->id,
            'title' => 'Máster en Formación del Profesorado',
            'institution' => 'Universidad de Demostración',
            'completion_year' => 2024,
            'notes' => 'Datos ficticios.',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Formación académica añadida correctamente.');
        $this->assertDatabaseHas('qualifications', [
            'teacher_id' => $teacher->id,
            'title' => 'Máster en Formación del Profesorado',
            'completion_year' => 2024,
        ]);
        $this->assertDatabaseMissing('qualifications', [
            'teacher_id' => $otherTeacher->id,
        ]);
    }

    public function test_rejects_invalid_qualification(): void
    {
        $this->travelTo('2026-09-23');
        $teacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.qualifications.store', $teacher), [
            'completion_year' => 2027,
        ]);

        $response->assertSessionHasErrors([
            'title' => 'El título o estudio es obligatorio.',
            'completion_year' => 'El año de finalización debe estar entre 1900 y el año actual.',
        ]);
        $this->assertDatabaseCount('qualifications', 0);
    }

    public function test_renders_edit_form_with_qualification_data(): void
    {
        $teacher = Teacher::factory()->create();
        $qualification = Qualification::factory()->for($teacher)->create([
            'title' => 'Certificado de docencia',
        ]);

        $response = $this->get(
            route('teachers.qualifications.edit', [$teacher, $qualification]),
        );

        $response->assertOk()
            ->assertSee('value="Certificado de docencia"', false);
    }

    public function test_updates_teacher_qualification(): void
    {
        $teacher = Teacher::factory()->create();
        $qualification = Qualification::factory()->for($teacher)->create();

        $response = $this->put(
            route('teachers.qualifications.update', [$teacher, $qualification]),
            [
                'title' => 'Grado en Matemáticas',
                'institution' => 'Universidad de Demostración',
                'completion_year' => 2022,
                'notes' => 'Actualizado.',
            ],
        );

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Formación académica actualizada correctamente.');
        $this->assertDatabaseHas('qualifications', [
            'id' => $qualification->id,
            'title' => 'Grado en Matemáticas',
            'completion_year' => 2022,
            'notes' => 'Actualizado.',
        ]);
    }

    public function test_returns_404_for_qualification_from_another_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $qualification = Qualification::factory()->for($otherTeacher)->create();

        $response = $this->delete(
            route('teachers.qualifications.destroy', [$teacher, $qualification]),
        );

        $response->assertNotFound();
        $this->assertModelExists($qualification);
    }

    public function test_deletes_only_selected_qualification(): void
    {
        $teacher = Teacher::factory()->create();
        $qualification = Qualification::factory()->for($teacher)->create();
        $remainingQualification = Qualification::factory()->for($teacher)->create();

        $response = $this->delete(
            route('teachers.qualifications.destroy', [$teacher, $qualification]),
        );

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Formación académica eliminada correctamente.');
        $this->assertModelMissing($qualification);
        $this->assertModelExists($remainingQualification);
    }
}
