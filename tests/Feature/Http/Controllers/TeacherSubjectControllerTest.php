<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class TeacherSubjectControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_associates_existing_subject_with_details(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->post(route('teachers.subjects.store', $teacher), [
            'subject_id' => $subject->id,
            'details' => 'Imparte nivel avanzado.',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Materia asociada correctamente.');
        $this->assertDatabaseHas('subject_teacher', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'details' => 'Imparte nivel avanzado.',
        ]);
    }

    public function test_creates_and_associates_new_subject(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.subjects.store', $teacher), [
            'subject_name' => 'Programación web',
            'subject_description' => 'Materia de prueba.',
        ]);

        $subject = Subject::query()->sole();
        $response->assertRedirectToRoute('teachers.show', $teacher);
        $this->assertDatabaseHas('subjects', [
            'name' => 'Programación web',
            'description' => 'Materia de prueba.',
        ]);
        $this->assertDatabaseHas('subject_teacher', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_rejects_duplicate_association(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $teacher->subjects()->attach($subject);

        $response = $this->post(route('teachers.subjects.store', $teacher), [
            'subject_id' => $subject->id,
        ]);

        $response->assertSessionHasErrors([
            'subject_id' => 'El profesor ya tiene asociada esa materia.',
        ]);
        $this->assertDatabaseCount('subject_teacher', 1);
    }

    public function test_updates_pivot_details(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $teacher->subjects()->attach($subject, ['details' => 'Anterior']);

        $response = $this->patch(
            route('teachers.subjects.update', [$teacher, $subject]),
            ['details' => 'Actualizado'],
        );

        $response->assertRedirectToRoute('teachers.show', $teacher);
        $this->assertDatabaseHas('subject_teacher', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'details' => 'Actualizado',
        ]);
    }

    public function test_returns_404_for_subject_not_associated_with_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->patch(
            route('teachers.subjects.update', [$teacher, $subject]),
            ['details' => 'No permitido'],
        );

        $response->assertNotFound();
        $this->assertDatabaseMissing('subject_teacher', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_detaches_subject_without_deleting_it(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $teacher->subjects()->attach($subject);

        $response = $this->delete(route('teachers.subjects.destroy', [$teacher, $subject]));

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Materia desasociada correctamente.');
        $this->assertDatabaseMissing('subject_teacher', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);
        $this->assertModelExists($subject);
    }
}
