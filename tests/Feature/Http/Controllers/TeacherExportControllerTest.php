<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Availability;
use App\Models\Document;
use App\Models\PricingUnit;
use App\Models\Qualification;
use App\Models\Service;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use App\Models\TeacherTool;
use App\Models\User;
use App\Support\TeacherExportFormatter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherExportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_individual_csv_contains_approved_data_and_neutralizes_formulas(): void
    {
        $this->travelTo('2026-09-30 10:00:00');
        $teacher = Teacher::factory()->create([
            'first_name' => '=Fórmula',
            'last_names' => 'Profesora Ficticia',
            'identity_document' => 'TEST12345678',
            'email' => 'ficticia@example.test',
            'internal_notes' => 'SECRETO INTERNO NO EXPORTABLE',
        ]);
        $subject = Subject::factory()->create(['name' => 'Materia ficticia']);
        $teacher->subjects()->attach($subject, ['details' => 'Detalle de prueba']);
        Availability::factory()->for($teacher)->create([
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'modality' => 'online',
        ]);
        Qualification::factory()->for($teacher)->create([
            'title' => 'Título ficticio',
            'institution' => 'Centro de prueba',
            'completion_year' => 2025,
        ]);
        TeacherTool::factory()->for($teacher)->create([
            'category' => 'ai',
            'tool_name' => 'Herramienta ficticia',
            'level' => 'Avanzado',
        ]);
        $service = Service::factory()->create(['name' => 'Servicio ficticio']);
        $unit = PricingUnit::factory()->create(['name' => 'Hora ficticia']);
        TeacherServicePrice::factory()->for($teacher)->create([
            'service_id' => $service->id,
            'pricing_unit_id' => $unit->id,
            'net_amount' => 42.50,
            'valid_from' => '2026-09-01',
        ]);
        Document::factory()->for($teacher)->create(['original_name' => 'privado-ficticio.pdf']);

        $response = $this->get(route('teachers.export-one.csv', $teacher));

        $response->assertOk()
            ->assertDownload('profesor-2026-09-30.csv')
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $rows = $this->parseCsv($content);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertCount(2, $rows);
        $this->assertSame("'=Fórmula", $rows[1][array_search('Nombre', $rows[0], true)]);
        $this->assertStringContainsString('Materia ficticia - Detalle de prueba', $content);
        $this->assertStringContainsString('Lunes, 09:00-11:00, En línea', $content);
        $this->assertStringContainsString('Título ficticio', $content);
        $this->assertStringContainsString('Herramienta ficticia', $content);
        $this->assertStringContainsString('Servicio ficticio', $content);
        $this->assertStringNotContainsString('SECRETO INTERNO NO EXPORTABLE', $content);
        $this->assertStringNotContainsString('privado-ficticio.pdf', $content);
    }

    public function test_individual_pdf_downloads_and_its_template_excludes_private_content(): void
    {
        $this->travelTo('2026-09-30 10:00:00');
        $teacher = Teacher::factory()->create([
            'first_name' => 'Andrea',
            'last_names' => 'Docente Ficticia',
            'internal_notes' => 'OBSERVACIÓN INTERNA PRIVADA',
        ]);
        $subject = Subject::factory()->create(['name' => 'Materia PDF']);
        $teacher->subjects()->attach($subject);
        Document::factory()->for($teacher)->create(['original_name' => 'adjunto-privado.pdf']);

        $response = $this->get(route('teachers.export-one.pdf', $teacher));

        $response->assertOk()
            ->assertDownload('profesor-2026-09-30.pdf')
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $teacher->load(['subjects', 'availabilities', 'qualifications', 'teacherTools', 'teacherServicePrices']);
        $html = view('teachers.exports.show-pdf', app(TeacherExportFormatter::class)->individualData($teacher))->render();

        $this->assertStringContainsString('Andrea Docente Ficticia', $html);
        $this->assertStringContainsString('Materia PDF', $html);
        $this->assertStringNotContainsString('OBSERVACIÓN INTERNA PRIVADA', $html);
        $this->assertStringNotContainsString('adjunto-privado.pdf', $html);
    }

    public function test_general_csv_reuses_filters_and_exports_all_matching_results_beyond_pagination(): void
    {
        $subject = Subject::factory()->create(['name' => 'Materia exportable']);
        Teacher::factory()->count(16)->create([
            'first_name' => 'Coincidente',
            'status' => 'active',
        ])->each(fn (Teacher $teacher) => $teacher->subjects()->attach($subject));
        Teacher::factory()->create(['first_name' => 'No incluida', 'status' => 'archived']);

        $response = $this->get(route('teachers.export.csv', [
            'q' => 'Coincidente',
            'status' => 'active',
            'subject_id' => $subject->id,
        ]));

        $response->assertOk()->assertDownload('profesores-'.now()->format('Y-m-d').'.csv');
        $content = $response->streamedContent();
        $rows = $this->parseCsv($content);

        $this->assertCount(17, $rows);
        $this->assertSame('Apellidos', $rows[0][0]);
        $this->assertStringContainsString('Materia exportable', $content);
        $this->assertStringNotContainsString('No incluida', $content);
    }

    public function test_general_pdf_downloads_a_summary_without_sensitive_fields(): void
    {
        $teacher = Teacher::factory()->create([
            'first_name' => 'Resumen',
            'last_names' => 'Ficticio',
            'identity_document' => 'TEST87654321',
            'internal_notes' => 'NOTA PRIVADA NO EXPORTABLE',
        ]);

        $response = $this->get(route('teachers.export.pdf'));

        $response->assertOk()
            ->assertDownload('profesores-'.now()->format('Y-m-d').'.pdf')
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $teacher->load('subjects');
        $row = app(TeacherExportFormatter::class)->summaryRow($teacher);
        $html = view('teachers.exports.index-pdf', [
            'rows' => collect([$row]),
            'generatedAt' => now()->format('d/m/Y'),
        ])->render();

        $this->assertStringContainsString('Resumen', $html);
        $this->assertStringNotContainsString('TEST87654321', $html);
        $this->assertStringNotContainsString('NOTA PRIVADA NO EXPORTABLE', $html);
    }

    public function test_empty_filtered_export_redirects_with_a_useful_message(): void
    {
        foreach (['pdf', 'csv'] as $format) {
            $this->get(route('teachers.export.'.$format, ['q' => 'Sin coincidencias']))
                ->assertRedirectToRoute('teachers.index', ['q' => 'Sin coincidencias'])
                ->assertSessionHas('error', 'No hay profesores que coincidan con los criterios seleccionados para exportar.');
        }
    }

    public function test_exports_do_not_write_persistent_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $teacher = Teacher::factory()->create();

        $this->get(route('teachers.export-one.pdf', $teacher))->assertOk();
        $this->get(route('teachers.export-one.csv', $teacher))->assertOk();

        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_all_export_routes_require_authentication(): void
    {
        $teacher = Teacher::factory()->create();
        auth()->logout();

        $this->get(route('teachers.export.pdf'))->assertRedirectToRoute('login');
        $this->get(route('teachers.export.csv'))->assertRedirectToRoute('login');
        $this->get(route('teachers.export-one.pdf', $teacher))->assertRedirectToRoute('login');
        $this->get(route('teachers.export-one.csv', $teacher))->assertRedirectToRoute('login');
    }

    /** @return array<int, array<int, string>> */
    private function parseCsv(string $content): array
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, substr($content, 3));
        rewind($stream);
        $rows = [];

        while (($row = fgetcsv($stream, separator: ';', escape: '\\')) !== false) {
            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }
}
