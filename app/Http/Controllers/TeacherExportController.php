<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterTeachersRequest;
use App\Models\Teacher;
use App\Queries\TeacherIndexQuery;
use App\Support\TeacherExportFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherExportController extends Controller
{
    public function indexPdf(
        FilterTeachersRequest $request,
        TeacherIndexQuery $teacherIndexQuery,
        TeacherExportFormatter $formatter,
    ): Response|RedirectResponse {
        $filters = $request->validated();
        $teachers = $teacherIndexQuery->build($filters)
            ->with(['subjects' => fn ($query) => $query->orderBy('name')->orderBy('subjects.id')])
            ->get();

        if ($teachers->isEmpty()) {
            return $this->emptyResults($filters);
        }

        $rows = $teachers->map(fn (Teacher $teacher): array => $formatter->summaryRow($teacher));
        $pdf = Pdf::loadView('teachers.exports.index-pdf', [
            'rows' => $rows,
            'generatedAt' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->filename('profesores', 'pdf'));
    }

    public function indexCsv(
        FilterTeachersRequest $request,
        TeacherIndexQuery $teacherIndexQuery,
        TeacherExportFormatter $formatter,
    ): StreamedResponse|RedirectResponse {
        $filters = $request->validated();
        $teachers = $teacherIndexQuery->build($filters)
            ->with(['subjects' => fn ($query) => $query->orderBy('name')->orderBy('subjects.id')])
            ->get();

        if ($teachers->isEmpty()) {
            return $this->emptyResults($filters);
        }

        $rows = $teachers->map(fn (Teacher $teacher): array => $formatter->summaryRow($teacher));

        return $this->csv($rows->all(), $this->filename('profesores', 'csv'), $formatter);
    }

    public function showPdf(Teacher $teacher, TeacherExportFormatter $formatter): Response
    {
        $this->loadIndividualRelations($teacher);
        $pdf = Pdf::loadView('teachers.exports.show-pdf', $formatter->individualData($teacher))
            ->setPaper('a4', 'portrait');

        return $pdf->download($this->filename('profesor', 'pdf'));
    }

    public function showCsv(Teacher $teacher, TeacherExportFormatter $formatter): StreamedResponse
    {
        $this->loadIndividualRelations($teacher);

        return $this->csv(
            [$formatter->individualRow($teacher)],
            $this->filename('profesor', 'csv'),
            $formatter,
        );
    }

    /** @param array<int, array<string, string>> $rows */
    private function csv(array $rows, string $filename, TeacherExportFormatter $formatter): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $formatter): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");

            $write = function (array $row) use ($output, $formatter): void {
                fputcsv(
                    $output,
                    array_map($formatter->sanitizeCsvCell(...), $row),
                    ';',
                    '"',
                    '\\',
                    "\r\n",
                );
            };

            $write(array_keys($rows[0]));
            foreach ($rows as $row) {
                $write(array_values($row));
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function loadIndividualRelations(Teacher $teacher): void
    {
        $teacher->load([
            'subjects' => fn ($query) => $query->orderBy('name')->orderBy('subjects.id'),
            'availabilities' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time')->orderBy('id'),
            'qualifications' => fn ($query) => $query->orderByDesc('completion_year')->orderBy('title')->orderBy('id'),
            'teacherTools' => fn ($query) => $query->orderBy('category')->orderBy('tool_name')->orderBy('id'),
            'teacherServicePrices' => fn ($query) => $query
                ->with(['service', 'subject', 'pricingUnit'])
                ->orderByDesc('valid_from')
                ->orderByDesc('id'),
        ]);
    }

    /** @param array<string, mixed> $filters */
    private function emptyResults(array $filters): RedirectResponse
    {
        $query = collect($filters)
            ->reject(fn ($value) => $value === null || $value === '')
            ->all();

        return redirect()
            ->route('teachers.index', $query)
            ->with('error', 'No hay profesores que coincidan con los criterios seleccionados para exportar.');
    }

    private function filename(string $base, string $extension): string
    {
        return $base.'-'.now()->format('Y-m-d').'.'.$extension;
    }
}
