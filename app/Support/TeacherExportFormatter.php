<?php

namespace App\Support;

use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use Illuminate\Support\Carbon;

class TeacherExportFormatter
{
    /** @return array<string, string> */
    public function individualRow(Teacher $teacher): array
    {
        $data = $this->individualData($teacher);

        return [
            'Fecha de exportación' => now()->format('d/m/Y'),
            'Nombre' => $teacher->first_name,
            'Apellidos' => $teacher->last_names,
            ...$data['personal'],
            ...$data['contact'],
            ...$data['professional'],
            'Materias' => implode(' | ', $data['subjects']),
            'Disponibilidad' => implode(' | ', $data['availabilities']),
            'Formación académica' => implode(' | ', $data['qualifications']),
            'Herramientas' => implode(' | ', $data['tools']),
            'Tarifas' => implode(' | ', $data['prices']),
        ];
    }

    /** @return array<string, mixed> */
    public function individualData(Teacher $teacher): array
    {
        return [
            'name' => trim($teacher->first_name.' '.$teacher->last_names),
            'generatedAt' => now()->format('d/m/Y'),
            'personal' => [
                'Estado' => $this->status($teacher->status),
                'DNI/NIE' => $this->value($teacher->identity_document),
                'Fecha de nacimiento' => $teacher->birth_date?->format('d/m/Y') ?? '-',
                'Lugar de nacimiento' => $this->value($teacher->birth_place),
            ],
            'contact' => [
                'Correo electrónico' => $this->value($teacher->email),
                'Teléfono' => $this->value($teacher->phone),
                'Dirección' => $this->value($teacher->address),
                'Localidad' => $this->value($teacher->locality),
                'Código postal' => $this->value($teacher->postal_code),
                'Provincia' => $this->value($teacher->province),
                'País' => $this->value($teacher->country),
            ],
            'professional' => [
                'Años de experiencia' => $teacher->years_of_experience === null ? '-' : (string) $teacher->years_of_experience,
                'Crea contenido' => $teacher->creates_content ? 'Sí' : 'No',
                'Actualiza contenido' => $teacher->updates_content ? 'Sí' : 'No',
            ],
            'subjects' => $teacher->subjects->map(function ($subject): string {
                $details = trim((string) $subject->pivot->details);

                return $details === '' ? $subject->name : $subject->name.' - '.$details;
            })->values()->all(),
            'availabilities' => $teacher->availabilities->map(fn ($availability): string => sprintf(
                '%s, %s-%s, %s',
                $this->day((int) $availability->day_of_week),
                substr((string) $availability->start_time, 0, 5),
                substr((string) $availability->end_time, 0, 5),
                $this->modality($availability->modality),
            ))->values()->all(),
            'qualifications' => $teacher->qualifications->map(function ($qualification): string {
                $parts = [$qualification->title];

                if ($qualification->institution) {
                    $parts[] = $qualification->institution;
                }
                if ($qualification->completion_year) {
                    $parts[] = (string) $qualification->completion_year;
                }
                if ($qualification->notes) {
                    $parts[] = $qualification->notes;
                }

                return implode(' - ', $parts);
            })->values()->all(),
            'tools' => $teacher->teacherTools->map(fn ($tool): string => sprintf(
                '%s: %s - Nivel %s',
                $tool->category === 'ai' ? 'Inteligencia artificial' : 'Programa',
                $tool->tool_name,
                $tool->level,
            ))->values()->all(),
            'prices' => $teacher->teacherServicePrices->map(fn (TeacherServicePrice $price): string => $this->price($price))->values()->all(),
        ];
    }

    /** @return array<string, string> */
    public function summaryRow(Teacher $teacher): array
    {
        return [
            'Apellidos' => $teacher->last_names,
            'Nombre' => $teacher->first_name,
            'Correo electrónico' => $this->value($teacher->email),
            'Teléfono' => $this->value($teacher->phone),
            'Localidad' => $this->value($teacher->locality),
            'Provincia' => $this->value($teacher->province),
            'País' => $this->value($teacher->country),
            'Materias' => $teacher->subjects->pluck('name')->implode(', ') ?: '-',
            'Estado' => $this->status($teacher->status),
        ];
    }

    public function sanitizeCsvCell(mixed $value): string
    {
        $cell = preg_replace('/\R+/u', ' ', (string) $value) ?? '';

        if (trim($cell) === '-') {
            return $cell;
        }

        return preg_match('/^\s*[=+\-@]/u', $cell) === 1 ? "'".$cell : $cell;
    }

    private function price(TeacherServicePrice $price): string
    {
        $isGeneral = $price->subject_id === null && $price->modality === 'all';

        return sprintf(
            '%s; %s; %s; %s %s; materia: %s; modalidad: %s; %s; %s',
            $isGeneral ? 'General' : 'Específica',
            $price->service->name,
            $price->pricingUnit->name,
            number_format((float) $price->net_amount, 2, ',', '.'),
            $price->currency,
            $price->subject?->name ?? 'Todas',
            $this->modality($price->modality),
            $price->valid_from->format('d/m/Y').' - '.($price->valid_until?->format('d/m/Y') ?? 'Sin fecha de fin'),
            $this->priceStatus($price),
        );
    }

    private function priceStatus(TeacherServicePrice $price): string
    {
        $today = Carbon::today();

        if ($price->valid_from->isAfter($today)) {
            return 'Programada';
        }

        if ($price->valid_until?->isBefore($today)) {
            return 'Histórica';
        }

        return 'Vigente';
    }

    private function value(mixed $value): string
    {
        return $value === null || $value === '' ? '-' : (string) $value;
    }

    private function status(string $status): string
    {
        return match ($status) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'archived' => 'Archivado',
        };
    }

    private function modality(string $modality): string
    {
        return match ($modality) {
            'in_person' => 'Presencial',
            'online' => 'En línea',
            'all' => 'Ambas',
        };
    }

    private function day(int $day): string
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ][$day] ?? 'Día no indicado';
    }
}
