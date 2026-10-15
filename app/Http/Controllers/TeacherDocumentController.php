<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherDocumentRequest;
use App\Models\Document;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TeacherDocumentController extends Controller
{
    public function store(StoreTeacherDocumentRequest $request, Teacher $teacher): RedirectResponse
    {
        $file = $request->file('document');
        $extension = Str::lower($file->getClientOriginalExtension());
        $disk = 'local';
        $storagePath = $file->storeAs(
            "teachers/{$teacher->id}/documents",
            Str::uuid().'.'.$extension,
            $disk,
        );

        if ($storagePath === false) {
            throw new RuntimeException('No se pudo almacenar el documento.');
        }

        try {
            $teacher->documents()->create([
                'category' => $request->string('category')->toString(),
                'original_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'storage_path' => $storagePath,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'sha256' => hash_file('sha256', Storage::disk($disk)->path($storagePath)),
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($storagePath);

            throw $exception;
        }

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Documento subido correctamente.');
    }

    public function download(Teacher $teacher, Document $document): StreamedResponse
    {
        abort_unless(Storage::disk($document->disk)->exists($document->storage_path), 404);

        return Storage::disk($document->disk)->download(
            $document->storage_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type ?? 'application/octet-stream'],
        );
    }

    public function destroy(Teacher $teacher, Document $document): RedirectResponse
    {
        $disk = Storage::disk($document->disk);
        $originalPath = $document->storage_path;
        $quarantinePath = 'quarantine/documents/'.Str::uuid();

        if (! $disk->exists($originalPath)) {
            return redirect()
                ->route('teachers.show', $teacher)
                ->withErrors(['document' => 'No se puede eliminar el documento porque falta el archivo almacenado.']);
        }

        if (! $disk->move($originalPath, $quarantinePath)) {
            return redirect()
                ->route('teachers.show', $teacher)
                ->withErrors(['document' => 'No se pudo preparar el documento para su eliminación.']);
        }

        try {
            DB::transaction(fn () => $document->deleteOrFail());
        } catch (Throwable $exception) {
            if (! $disk->move($quarantinePath, $originalPath)) {
                Log::critical('No se pudo restaurar un documento tras fallar su eliminación en base de datos.', [
                    'document_id' => $document->getKey(),
                    'disk' => $document->disk,
                    'quarantine_path' => $quarantinePath,
                ]);

                return redirect()
                    ->route('teachers.show', $teacher)
                    ->withErrors(['document' => 'No se pudo eliminar el documento. El archivo se ha conservado para su recuperación.']);
            }

            Log::warning('Se restauró un documento tras fallar su eliminación en base de datos.', [
                'document_id' => $document->getKey(),
                'disk' => $document->disk,
            ]);

            return redirect()
                ->route('teachers.show', $teacher)
                ->withErrors(['document' => 'No se pudo eliminar el documento. El archivo original se ha restaurado.']);
        }

        if (! $disk->delete($quarantinePath)) {
            Log::warning('No se pudo purgar un documento eliminado conservado en cuarentena.', [
                'document_id' => $document->getKey(),
                'disk' => $document->disk,
                'quarantine_path' => $quarantinePath,
            ]);
        }

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Documento eliminado correctamente.');
    }
}
