<?php

namespace App\Http\Requests;

use App\Models\PricingUnit;
use App\Models\Service;
use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTeacherServicePriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists(Service::class, 'id')->where('is_active', true)],
            'pricing_unit_id' => ['required', 'integer', Rule::exists(PricingUnit::class, 'id')->where('is_active', true)],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'modality' => ['required', 'string', 'in:in_person,online,all'],
            'net_amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('subject_id') || $this->input('subject_id') === null) {
                    return;
                }

                $teacher = $this->route('teacher');

                if ($teacher instanceof Teacher && ! $teacher->subjects()->whereKey($this->integer('subject_id'))->exists()) {
                    $validator->errors()->add('subject_id', 'La materia no está asignada al profesor.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'service_id.required' => 'El servicio es obligatorio.',
            'service_id.exists' => 'El servicio seleccionado no está disponible.',
            'pricing_unit_id.required' => 'La unidad es obligatoria.',
            'pricing_unit_id.exists' => 'La unidad seleccionada no está disponible.',
            'subject_id.exists' => 'La materia seleccionada no es válida.',
            'modality.required' => 'La modalidad es obligatoria.',
            'modality.in' => 'La modalidad seleccionada no es válida.',
            'net_amount.required' => 'El importe es obligatorio.',
            'net_amount.numeric' => 'El importe debe ser numérico.',
            'net_amount.gt' => 'El importe debe ser mayor que cero.',
            'net_amount.decimal' => 'El importe puede tener como máximo dos decimales.',
            'valid_from.required' => 'La fecha de inicio es obligatoria.',
            'valid_from.date' => 'La fecha de inicio no es válida.',
            'valid_until.date' => 'La fecha de fin no es válida.',
            'valid_until.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ];
    }
}
