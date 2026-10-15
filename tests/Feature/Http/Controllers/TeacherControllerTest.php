<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Availability;
use App\Models\Document;
use App\Models\Qualification;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherTool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class TeacherControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    public function test_lists_teachers_in_name_order_without_loading_relations(): void
    {
        Teacher::factory()->create(['first_name' => 'Berta', 'last_names' => 'Zuluaga', 'status' => 'archived']);
        Teacher::factory()->create(['first_name' => 'Ana', 'last_names' => 'Alonso']);

        $response = $this->get(route('teachers.index'));

        $response->assertOk()
            ->assertViewHas('teachers')
            ->assertSeeInOrder(['Alonso', 'Zuluaga']);
    }

    public function test_searches_normalized_full_name_and_other_text_fields(): void
    {
        $matching = Teacher::factory()->create([
            'first_name' => 'Ana María',
            'last_names' => 'García López',
            'identity_document' => 'TEST12345678',
            'email' => 'ana@example.com',
            'phone' => '600123123',
            'locality' => 'Sevilla',
            'postal_code' => '41001',
            'province' => 'Sevilla provincia',
            'country' => 'España ficticia',
            'internal_notes' => 'Disponible para proyecto ficticio Atlas.',
        ]);
        $subject = Subject::factory()->create(['name' => 'Programación web']);
        $matching->subjects()->attach($subject);
        Teacher::factory()->create(['first_name' => 'Beatriz', 'last_names' => 'Martín']);

        foreach (['  Ana   García  ', 'TEST12345678', 'ana@example.com', '600123123', 'Sevilla', '41001', 'Sevilla provincia', 'España ficticia', 'Programación', 'Atlas'] as $search) {
            $this->get(route('teachers.index', ['q' => $search]))
                ->assertOk()
                ->assertViewHas('teachers', fn ($teachers) => $teachers->pluck('id')->all() === [$matching->id])
                ->assertSee('value="'.trim(preg_replace('/\s+/u', ' ', $search)).'"', false);
        }
    }

    public function test_combines_status_subject_modality_and_content_filters(): void
    {
        $subject = Subject::factory()->create();
        $matching = Teacher::factory()->create([
            'status' => 'active',
            'creates_content' => true,
            'updates_content' => false,
        ]);
        $matching->subjects()->attach($subject);
        Availability::factory()->for($matching)->create(['modality' => 'online']);
        Teacher::factory()->create([
            'status' => 'active',
            'creates_content' => true,
            'updates_content' => true,
        ])->subjects()->attach($subject);

        $this->get(route('teachers.index', [
            'status' => 'active',
            'subject_id' => $subject->id,
            'modality' => 'online',
            'creates_content' => '1',
            'updates_content' => '0',
        ]))->assertOk()
            ->assertViewHas('teachers', fn ($teachers) => $teachers->pluck('id')->all() === [$matching->id]);
    }

    public function test_modality_filters_include_teacher_available_in_both_modalities(): void
    {
        $both = Teacher::factory()->create(['first_name' => 'Ambas']);
        Availability::factory()->for($both)->create(['modality' => 'in_person']);
        Availability::factory()->for($both)->create(['modality' => 'online']);
        $onlineOnly = Teacher::factory()->create(['first_name' => 'Solo online']);
        Availability::factory()->for($onlineOnly)->create(['modality' => 'online']);

        $this->get(route('teachers.index', ['modality' => 'in_person']))
            ->assertViewHas('teachers', fn ($teachers) => $teachers->pluck('id')->all() === [$both->id]);

        $this->get(route('teachers.index', ['modality' => 'online']))
            ->assertViewHas('teachers', fn ($teachers) => $teachers->pluck('id')->sort()->values()->all() === [$both->id, $onlineOnly->id]);
    }

    public function test_subject_selector_only_contains_assigned_subjects(): void
    {
        $assigned = Subject::factory()->create(['name' => 'Materia asociada']);
        Subject::factory()->create(['name' => 'Materia sin profesores']);
        Teacher::factory()->create()->subjects()->attach($assigned);

        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertViewHas('subjects', fn ($subjects) => $subjects->pluck('id')->all() === [$assigned->id])
            ->assertSeeText('Materia asociada')
            ->assertDontSeeText('Materia sin profesores');
    }

    public function test_pagination_preserves_search_and_filters(): void
    {
        Teacher::factory()->count(16)->create([
            'first_name' => 'Filtro',
            'status' => 'active',
        ]);

        $this->get(route('teachers.index', ['q' => 'Filtro', 'status' => 'active']))
            ->assertOk()
            ->assertViewHas('teachers', function ($teachers): bool {
                $nextPageUrl = $teachers->nextPageUrl();

                return $teachers->count() === 15
                    && str_contains($nextPageUrl, 'q=Filtro')
                    && str_contains($nextPageUrl, 'status=active');
            });
    }

    public function test_shows_filtered_empty_state_and_clear_filters_link(): void
    {
        Teacher::factory()->create(['first_name' => 'Ana']);

        $this->get(route('teachers.index', ['q' => 'Inexistente']))
            ->assertOk()
            ->assertSeeText('No se encontraron profesores con los criterios indicados.')
            ->assertSeeText('Limpiar filtros')
            ->assertSee('href="'.route('teachers.index').'"', false);
    }

    public function test_renders_creation_form(): void
    {
        $response = $this->get(route('teachers.create'));

        $response->assertOk()
            ->assertSeeText('Añadir profesor');
    }

    public function test_valid_payload_creates_active_teacher(): void
    {
        $payload = [
            'first_name' => 'Lucía',
            'last_names' => 'Martín López',
            'identity_document' => '  x 1234 567 l ',
            'email' => 'lucia@example.com',
            'postal_code' => 'SW1A 1AA',
            'province' => 'Provincia ficticia',
            'country' => 'País ficticio',
            'years_of_experience' => 8,
            'creates_content' => '1',
            'internal_notes' => 'Observación ficticia para pruebas.',
        ];

        $response = $this->post(route('teachers.store'), $payload);

        $teacher = Teacher::query()->sole();
        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Profesor creado correctamente.');
        $this->assertDatabaseHas('teachers', [
            'first_name' => 'Lucía',
            'last_names' => 'Martín López',
            'identity_document' => 'X1234567L',
            'postal_code' => 'SW1A 1AA',
            'province' => 'Provincia ficticia',
            'country' => 'País ficticio',
            'status' => 'active',
            'creates_content' => true,
            'updates_content' => false,
            'internal_notes' => 'Observación ficticia para pruebas.',
        ]);
    }

    public function test_identity_document_is_unique_when_present_and_allows_multiple_nulls(): void
    {
        Teacher::factory()->create(['identity_document' => 'TEST12345678']);
        Teacher::factory()->create(['identity_document' => null]);
        Teacher::factory()->create(['identity_document' => null]);

        $this->post(route('teachers.store'), [
            'first_name' => 'Nombre',
            'last_names' => 'Duplicado',
            'identity_document' => ' test 12345678 ',
        ])->assertSessionHasErrors([
            'identity_document' => 'Ya existe un profesor con ese DNI/NIE.',
        ]);

        $this->assertDatabaseCount('teachers', 3);
    }

    public function test_rejects_internal_notes_over_five_thousand_characters(): void
    {
        $this->post(route('teachers.store'), [
            'first_name' => 'Nombre',
            'last_names' => 'Ficticio',
            'internal_notes' => str_repeat('a', 5001),
        ])->assertSessionHasErrors([
            'internal_notes' => 'Las observaciones internas no pueden superar los 5.000 caracteres.',
        ]);

        $this->assertDatabaseCount('teachers', 0);
    }

    public function test_preserves_contact_values_after_validation_error(): void
    {
        $this->from(route('teachers.create'))->post(route('teachers.store'), [
            'postal_code' => 'H0H 0H0',
            'province' => 'Provincia de prueba',
            'country' => 'País de prueba',
        ])->assertRedirect(route('teachers.create'))
            ->assertSessionHasInput('postal_code', 'H0H 0H0')
            ->assertSessionHasInput('province', 'Provincia de prueba')
            ->assertSessionHasInput('country', 'País de prueba');
    }

    public function test_rejects_invalid_teacher_data_with_spanish_messages(): void
    {
        $response = $this->post(route('teachers.store'), [
            'birth_date' => now()->addDay()->toDateString(),
            'years_of_experience' => 101,
        ]);

        $response->assertSessionHasErrors([
            'first_name' => 'El nombre es obligatorio.',
            'last_names' => 'Los apellidos son obligatorios.',
            'birth_date' => 'La fecha de nacimiento no puede ser futura.',
            'years_of_experience' => 'Los años de experiencia deben estar entre 0 y 100.',
        ]);
        $this->assertDatabaseCount('teachers', 0);
    }

    public function test_shows_teacher_and_escapes_user_content(): void
    {
        $teacher = Teacher::factory()->create([
            'first_name' => '<script>alert("xss")</script>',
            'last_names' => 'Prueba',
        ]);

        $response = $this->get(route('teachers.show', $teacher));

        $response->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_shows_qualifications_from_newest_to_oldest(): void
    {
        $teacher = Teacher::factory()->create();
        Qualification::factory()->for($teacher)->create([
            'title' => 'Formación antigua',
            'completion_year' => 2020,
        ]);
        Qualification::factory()->for($teacher)->create([
            'title' => 'Formación reciente',
            'completion_year' => 2025,
        ]);

        $response = $this->get(route('teachers.show', $teacher));

        $response->assertOk()
            ->assertSeeInOrder(['Formación reciente', 'Formación antigua']);
    }

    public function test_shows_teacher_tools_grouped_and_ordered_by_name(): void
    {
        $teacher = Teacher::factory()->create();
        TeacherTool::factory()->for($teacher)->create([
            'category' => 'software',
            'tool_name' => 'Microsoft Word',
        ]);
        TeacherTool::factory()->for($teacher)->create([
            'category' => 'software',
            'tool_name' => 'Microsoft Excel',
        ]);
        TeacherTool::factory()->for($teacher)->create([
            'category' => 'ai',
            'tool_name' => 'ChatGPT',
        ]);

        $response = $this->get(route('teachers.show', $teacher));

        $response->assertOk()
            ->assertSeeInOrder([
                'Programas',
                'Microsoft Excel',
                'Microsoft Word',
                'Inteligencia artificial',
                'ChatGPT',
            ]);
    }

    public function test_shows_documents_newest_first_and_escapes_original_name(): void
    {
        $teacher = Teacher::factory()->create();
        $this->travelTo('2026-09-23 10:00:00');
        Document::factory()->for($teacher)->create([
            'original_name' => 'documento-antiguo.pdf',
        ]);
        $this->travelTo('2026-09-23 11:00:00');
        Document::factory()->for($teacher)->create([
            'original_name' => '<script>alert("xss")</script>.pdf',
        ]);

        $response = $this->get(route('teachers.show', $teacher));

        $response->assertOk()
            ->assertSeeInOrder(['&lt;script&gt;', 'documento-antiguo.pdf'], false)
            ->assertDontSee('<script>alert("xss")</script>.pdf', false);
    }

    public function test_renders_edit_form_with_teacher_data(): void
    {
        $teacher = Teacher::factory()->create([
            'first_name' => 'Elena',
            'identity_document' => 'TEST87654321',
            'postal_code' => '00000-TEST',
            'province' => 'Provincia inicial',
            'country' => 'País inicial',
            'internal_notes' => 'Nota interna ficticia.',
        ]);

        $response = $this->get(route('teachers.edit', $teacher));

        $response->assertOk()
            ->assertSee('value="Elena"', false)
            ->assertSee('value="TEST87654321"', false)
            ->assertSee('value="00000-TEST"', false)
            ->assertSee('value="Provincia inicial"', false)
            ->assertSee('value="País inicial"', false)
            ->assertSeeText('Nota interna ficticia.');
    }

    public function test_updates_teacher_without_allowing_status_change(): void
    {
        $teacher = Teacher::factory()->create([
            'first_name' => 'Nombre anterior',
            'identity_document' => 'TEST11223344',
            'status' => 'active',
        ]);

        $response = $this->put(route('teachers.update', $teacher), [
            'first_name' => 'Nombre nuevo',
            'last_names' => $teacher->last_names,
            'identity_document' => ' test 1122 3344 ',
            'postal_code' => 'EC1A 1BB',
            'province' => 'Provincia actualizada',
            'country' => 'País actualizado',
            'internal_notes' => 'Observaciones actualizadas.',
            'status' => 'archived',
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Profesor actualizado correctamente.');
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'first_name' => 'Nombre nuevo',
            'identity_document' => 'TEST11223344',
            'postal_code' => 'EC1A 1BB',
            'province' => 'Provincia actualizada',
            'country' => 'País actualizado',
            'status' => 'active',
            'internal_notes' => 'Observaciones actualizadas.',
        ]);
    }

    public function test_sensitive_fields_are_not_shown_in_general_list_and_detail_requires_authentication(): void
    {
        $teacher = Teacher::factory()->create([
            'identity_document' => 'TEST99887766',
            'postal_code' => 'TEST-28001',
            'province' => 'Provincia visible',
            'country' => 'País visible',
            'internal_notes' => 'Observación privada ficticia.',
        ]);

        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertDontSeeText('TEST99887766')
            ->assertDontSeeText('Observación privada ficticia.');

        $this->get(route('teachers.show', $teacher))
            ->assertOk()
            ->assertSeeText('TEST99887766')
            ->assertSeeText('TEST-28001')
            ->assertSeeText('Provincia visible')
            ->assertSeeText('País visible')
            ->assertSeeText('Observación privada ficticia.');

        auth()->logout();

        $this->get(route('teachers.show', $teacher))->assertRedirectToRoute('login');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validStatuses(): array
    {
        return [
            'active' => ['active'],
            'inactive' => ['inactive'],
            'archived' => ['archived'],
        ];
    }

    #[DataProvider('validStatuses')]
    public function test_changes_teacher_to_each_valid_status(string $status): void
    {
        $teacher = Teacher::factory()->create(['status' => 'active']);

        $response = $this->patch(route('teachers.update-status', $teacher), [
            'status' => $status,
        ]);

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Estado actualizado correctamente.');
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => $status,
        ]);
    }

    public function test_rejects_unknown_status_without_changing_teacher(): void
    {
        $teacher = Teacher::factory()->create(['status' => 'active']);

        $response = $this->patch(route('teachers.update-status', $teacher), [
            'status' => 'deleted',
        ]);

        $response->assertSessionHasErrors([
            'status' => 'El estado seleccionado no es válido.',
        ]);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'status' => 'active',
        ]);
    }

    public function test_destroy_route_is_not_available(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->delete('/teachers/'.$teacher->id);

        $response->assertMethodNotAllowed();
        $this->assertModelExists($teacher);
    }
}
