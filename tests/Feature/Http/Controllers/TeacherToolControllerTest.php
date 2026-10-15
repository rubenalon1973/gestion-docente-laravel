<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherTool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class TeacherToolControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_adds_valid_tool_to_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.tools.store', $teacher), [
            'teacher_id' => $otherTeacher->id,
            'category' => 'ai',
            'tool_name' => 'ChatGPT',
            'level' => 'intermedio',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Programa o herramienta añadida correctamente.');
        $this->assertDatabaseHas('teacher_tools', [
            'teacher_id' => $teacher->id,
            'category' => 'ai',
            'tool_name' => 'ChatGPT',
            'level' => 'intermedio',
        ]);
        $this->assertDatabaseMissing('teacher_tools', [
            'teacher_id' => $otherTeacher->id,
        ]);
    }

    public function test_rejects_invalid_tool_data(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->post(route('teachers.tools.store', $teacher), [
            'category' => 'other',
            'level' => 'experto',
        ]);

        $response->assertSessionHasErrors([
            'category' => 'La categoría seleccionada no es válida.',
            'tool_name' => 'El nombre del programa o herramienta es obligatorio.',
            'level' => 'El nivel seleccionado no es válido.',
        ]);
        $this->assertDatabaseCount('teacher_tools', 0);
    }

    public function test_rejects_duplicate_tool_in_same_category(): void
    {
        $teacher = Teacher::factory()->create();
        TeacherTool::factory()->for($teacher)->create([
            'category' => 'software',
            'tool_name' => 'Microsoft Excel',
        ]);

        $response = $this->post(route('teachers.tools.store', $teacher), [
            'category' => 'software',
            'tool_name' => 'Microsoft Excel',
            'level' => 'avanzado',
        ]);

        $response->assertSessionHasErrors([
            'tool_name' => 'El profesor ya tiene registrado ese programa o herramienta en la categoría seleccionada.',
        ]);
        $this->assertDatabaseCount('teacher_tools', 1);
    }

    public function test_renders_edit_form_with_tool_data(): void
    {
        $teacher = Teacher::factory()->create();
        $teacherTool = TeacherTool::factory()->for($teacher)->create([
            'tool_name' => 'Microsoft Word',
        ]);

        $response = $this->get(route('teachers.tools.edit', [$teacher, $teacherTool]));

        $response->assertOk()
            ->assertSee('value="Microsoft Word"', false);
    }

    public function test_updates_teacher_tool_without_rejecting_its_own_name(): void
    {
        $teacher = Teacher::factory()->create();
        $teacherTool = TeacherTool::factory()->for($teacher)->create([
            'category' => 'ai',
            'tool_name' => 'ChatGPT',
            'level' => 'básico',
        ]);

        $response = $this->put(
            route('teachers.tools.update', [$teacher, $teacherTool]),
            [
                'category' => 'ai',
                'tool_name' => 'ChatGPT',
                'level' => 'avanzado',
            ],
        );

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Programa o herramienta actualizado correctamente.');
        $this->assertDatabaseHas('teacher_tools', [
            'id' => $teacherTool->id,
            'category' => 'ai',
            'tool_name' => 'ChatGPT',
            'level' => 'avanzado',
        ]);
    }

    public function test_returns_404_for_tool_from_another_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $teacherTool = TeacherTool::factory()->for($otherTeacher)->create();

        $response = $this->delete(route('teachers.tools.destroy', [$teacher, $teacherTool]));

        $response->assertNotFound();
        $this->assertModelExists($teacherTool);
    }

    public function test_deletes_only_selected_tool(): void
    {
        $teacher = Teacher::factory()->create();
        $teacherTool = TeacherTool::factory()->for($teacher)->create();
        $remainingTool = TeacherTool::factory()->for($teacher)->create();

        $response = $this->delete(route('teachers.tools.destroy', [$teacher, $teacherTool]));

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Programa o herramienta eliminado correctamente.');
        $this->assertModelMissing($teacherTool);
        $this->assertModelExists($remainingTool);
    }
}
