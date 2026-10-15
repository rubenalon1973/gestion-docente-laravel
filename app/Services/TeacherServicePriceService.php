<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeacherServicePriceService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): TeacherServicePrice
    {
        return $this->persist(new TeacherServicePrice, $attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(TeacherServicePrice $price, array $attributes): TeacherServicePrice
    {
        return $this->persist($price, $attributes);
    }

    public function findApplicable(
        int $teacherId,
        int $serviceId,
        int $pricingUnitId,
        ?int $subjectId,
        string $modality,
        CarbonInterface|string $date,
    ): ?TeacherServicePrice {
        return TeacherServicePrice::query()
            ->where('teacher_id', $teacherId)
            ->where('service_id', $serviceId)
            ->where('pricing_unit_id', $pricingUnitId)
            ->where(function (Builder $query) use ($subjectId): void {
                $query->whereNull('subject_id');

                if ($subjectId !== null) {
                    $query->orWhere('subject_id', $subjectId);
                }
            })
            ->whereIn('modality', [$modality, 'all'])
            ->whereDate('valid_from', '<=', $date)
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', $date);
            })
            ->orderByRaw('CASE WHEN subject_id = ? THEN 0 WHEN subject_id IS NULL THEN 1 ELSE 2 END', [$subjectId ?? 0])
            ->orderByRaw('CASE WHEN modality = ? THEN 0 ELSE 1 END', [$modality])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function persist(TeacherServicePrice $price, array $attributes): TeacherServicePrice
    {
        $values = array_replace($price->getAttributes(), $attributes);

        Validator::make($values, [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'pricing_unit_id' => ['required', 'integer', 'exists:pricing_units,id'],
            'modality' => ['required', 'in:in_person,online,all'],
            'net_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['required', 'in:EUR'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ])->validate();

        return DB::transaction(function () use ($price, $values): TeacherServicePrice {
            $teacher = Teacher::query()
                ->whereKey($values['teacher_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($values['subject_id'] !== null && ! $teacher->subjects()->whereKey($values['subject_id'])->exists()) {
                throw ValidationException::withMessages([
                    'subject_id' => 'La materia no está asignada al profesor.',
                ]);
            }

            $overlap = TeacherServicePrice::query()
                ->when($price->exists, fn (Builder $query) => $query->whereKeyNot($price->getKey()))
                ->where('teacher_id', $values['teacher_id'])
                ->where('service_id', $values['service_id'])
                ->where('pricing_unit_id', $values['pricing_unit_id'])
                ->where('modality', $values['modality'])
                ->when(
                    $values['subject_id'] === null,
                    fn (Builder $query) => $query->whereNull('subject_id'),
                    fn (Builder $query) => $query->where('subject_id', $values['subject_id'])
                )
                ->whereDate('valid_from', '<=', $values['valid_until'] ?? '9999-12-31')
                ->where(function (Builder $query) use ($values): void {
                    $query->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', $values['valid_from']);
                })
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'valid_from' => 'La vigencia se solapa con otra tarifa del mismo ámbito.',
                ]);
            }

            $price->fill($values)->save();

            return $price->refresh();
        });
    }
}
