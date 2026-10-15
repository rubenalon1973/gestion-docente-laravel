<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Document;
use App\Models\Teacher;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class TeacherDocumentControllerTest extends AuthenticatedControllerTestCase
{
    use LazilyRefreshDatabase;

    private const PDF_CONTENT = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF";

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function representativeAllowedFiles(): array
    {
        return [
            'cv docx' => ['cv', 'curriculum.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'photo webp' => ['photo', 'retrato.webp', 'image/webp'],
            'qualification jpeg' => ['qualification', 'titulo.jpeg', 'image/jpeg'],
            'other png' => ['other', 'anexo.png', 'image/png'],
        ];
    }

    public function test_uploads_private_document_with_opaque_name_and_metadata(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $file = UploadedFile::fake()->createWithContent('curriculum-demo.pdf', self::PDF_CONTENT);

        $response = $this->post(route('teachers.documents.store', $teacher), [
            'category' => 'cv',
            'document' => $file,
        ]);

        $document = Document::query()->sole();
        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Documento subido correctamente.');
        Storage::disk('local')->assertExists($document->storage_path);
        $this->assertMatchesRegularExpression(
            '#^teachers/'.$teacher->id.'/documents/[0-9a-f-]{36}\.pdf$#',
            $document->storage_path,
        );
        $this->assertStringNotContainsString('curriculum-demo', $document->storage_path);
        $this->assertSame('curriculum-demo.pdf', $document->original_name);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame(hash('sha256', self::PDF_CONTENT), $document->sha256);
    }

    #[DataProvider('representativeAllowedFiles')]
    public function test_accepts_approved_format_for_each_category(
        string $category,
        string $fileName,
        string $mimeType,
    ): void {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $file = UploadedFile::fake()->create($fileName, 1, $mimeType);

        $response = $this->post(route('teachers.documents.store', $teacher), [
            'category' => $category,
            'document' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('documents', [
            'teacher_id' => $teacher->id,
            'category' => $category,
            'mime_type' => $mimeType,
        ]);
    }

    public function test_rejects_file_when_extension_mime_and_category_are_incompatible(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $file = UploadedFile::fake()
            ->createWithContent('fotografia.jpg', self::PDF_CONTENT)
            ->mimeType('application/pdf');

        $response = $this->post(route('teachers.documents.store', $teacher), [
            'category' => 'photo',
            'document' => $file,
        ]);

        $response->assertSessionHasErrors([
            'document' => 'La extensión, el tipo real del archivo y la categoría seleccionada no son compatibles.',
        ]);
        $this->assertDatabaseCount('documents', 0);
        Storage::disk('local')->assertDirectoryEmpty('/');
    }

    public function test_rejects_format_not_approved_for_selected_category(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $file = UploadedFile::fake()->create('anexo.webp', 1, 'image/webp');

        $response = $this->post(route('teachers.documents.store', $teacher), [
            'category' => 'other',
            'document' => $file,
        ]);

        $response->assertSessionHasErrors('document');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_rejects_unapproved_extension(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $file = UploadedFile::fake()->createWithContent('notas.txt', 'Texto no permitido');

        $response = $this->post(route('teachers.documents.store', $teacher), [
            'category' => 'other',
            'document' => $file,
        ]);

        $response->assertSessionHasErrors('document');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_rejects_file_larger_than_ten_megabytes(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $file = UploadedFile::fake()->create('curriculum.pdf', 10_241, 'application/pdf');

        $response = $this->post(route('teachers.documents.store', $teacher), [
            'category' => 'cv',
            'document' => $file,
        ]);

        $response->assertSessionHasErrors([
            'document' => 'El archivo no puede superar los 10 MB.',
        ]);
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_downloads_document_only_through_application_route(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        Storage::disk('local')->put('teachers/1/documents/opaque.pdf', self::PDF_CONTENT);
        $document = Document::factory()->for($teacher)->create([
            'original_name' => 'curriculum.pdf',
            'disk' => 'local',
            'storage_path' => 'teachers/1/documents/opaque.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->get(route('teachers.documents.download', [$teacher, $document]));

        $response->assertOk()
            ->assertDownload('curriculum.pdf');
        $this->assertSame(self::PDF_CONTENT, $response->streamedContent());
    }

    public function test_returns_404_for_document_from_another_teacher(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $document = Document::factory()->for($otherTeacher)->create();

        $response = $this->get(
            route('teachers.documents.download', [$teacher, $document]),
        );

        $response->assertNotFound();
    }

    public function test_deletes_physical_file_and_document_record(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        Storage::disk('local')->put('teachers/1/documents/opaque.pdf', self::PDF_CONTENT);
        $document = Document::factory()->for($teacher)->create([
            'disk' => 'local',
            'storage_path' => 'teachers/1/documents/opaque.pdf',
        ]);

        $response = $this->delete(route('teachers.documents.destroy', [$teacher, $document]));

        $response->assertRedirectToRoute('teachers.show', $teacher)
            ->assertSessionHas('success', 'Documento eliminado correctamente.');
        Storage::disk('local')->assertMissing('teachers/1/documents/opaque.pdf');
        Storage::disk('local')->assertDirectoryEmpty('quarantine/documents');
        $this->assertModelMissing($document);
    }

    public function test_keeps_record_when_physical_file_is_missing(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $document = Document::factory()->for($teacher)->create([
            'disk' => 'local',
            'storage_path' => 'teachers/1/documents/missing.pdf',
        ]);

        $response = $this->delete(route('teachers.documents.destroy', [$teacher, $document]));

        $response->assertSessionHasErrors([
            'document' => 'No se puede eliminar el documento porque falta el archivo almacenado.',
        ]);
        $this->assertModelExists($document);
    }

    public function test_restores_file_when_database_deletion_fails(): void
    {
        Storage::fake('local');
        $teacher = Teacher::factory()->create();
        $path = 'teachers/1/documents/opaque.pdf';
        Storage::disk('local')->put($path, self::PDF_CONTENT);
        $document = Document::factory()->for($teacher)->create([
            'disk' => 'local',
            'storage_path' => $path,
        ]);
        Event::listen('eloquent.deleting: '.Document::class, fn () => throw new \RuntimeException('Fallo simulado'));

        try {
            $response = $this->delete(route('teachers.documents.destroy', [$teacher, $document]));
        } finally {
            Event::forget('eloquent.deleting: '.Document::class);
        }

        $response->assertSessionHasErrors([
            'document' => 'No se pudo eliminar el documento. El archivo original se ha restaurado.',
        ]);
        Storage::disk('local')->assertExists($path);
        Storage::disk('local')->assertDirectoryEmpty('quarantine/documents');
        $this->assertModelExists($document);
    }

    public function test_keeps_file_and_record_when_quarantine_move_fails(): void
    {
        $teacher = Teacher::factory()->create();
        $path = 'teachers/1/documents/opaque.pdf';
        $document = Document::factory()->for($teacher)->create([
            'disk' => 'local',
            'storage_path' => $path,
        ]);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->once()->with($path)->andReturnTrue();
        $disk->shouldReceive('move')->once()->with($path, Mockery::type('string'))->andReturnFalse();
        Storage::shouldReceive('disk')->once()->with('local')->andReturn($disk);

        $response = $this->delete(route('teachers.documents.destroy', [$teacher, $document]));

        $response->assertSessionHasErrors([
            'document' => 'No se pudo preparar el documento para su eliminación.',
        ]);
        $this->assertModelExists($document);
    }

    public function test_keeps_quarantine_and_logs_incident_when_restore_fails(): void
    {
        $teacher = Teacher::factory()->create();
        $path = 'teachers/1/documents/opaque.pdf';
        $document = Document::factory()->for($teacher)->create([
            'disk' => 'local',
            'storage_path' => $path,
        ]);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->once()->with($path)->andReturnTrue();
        $disk->shouldReceive('move')->once()->with($path, Mockery::type('string'))->andReturnTrue();
        $disk->shouldReceive('move')->once()->with(Mockery::type('string'), $path)->andReturnFalse();
        Storage::shouldReceive('disk')->once()->with('local')->andReturn($disk);
        Log::shouldReceive('critical')->once();
        Event::listen('eloquent.deleting: '.Document::class, fn () => throw new \RuntimeException('Fallo simulado'));

        try {
            $response = $this->delete(route('teachers.documents.destroy', [$teacher, $document]));
        } finally {
            Event::forget('eloquent.deleting: '.Document::class);
        }

        $response->assertSessionHasErrors([
            'document' => 'No se pudo eliminar el documento. El archivo se ha conservado para su recuperación.',
        ]);
        $this->assertModelExists($document);
    }

    public function test_keeps_quarantine_and_logs_warning_when_final_purge_fails(): void
    {
        $teacher = Teacher::factory()->create();
        $path = 'teachers/1/documents/opaque.pdf';
        $document = Document::factory()->for($teacher)->create([
            'disk' => 'local',
            'storage_path' => $path,
        ]);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->once()->with($path)->andReturnTrue();
        $disk->shouldReceive('move')->once()->with($path, Mockery::type('string'))->andReturnTrue();
        $disk->shouldReceive('delete')->once()->with(Mockery::type('string'))->andReturnFalse();
        Storage::shouldReceive('disk')->once()->with('local')->andReturn($disk);
        Log::shouldReceive('warning')->once();

        $response = $this->delete(route('teachers.documents.destroy', [$teacher, $document]));

        $response->assertSessionHas('success', 'Documento eliminado correctamente.');
        $this->assertModelMissing($document);
    }
}
