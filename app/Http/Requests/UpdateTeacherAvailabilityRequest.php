<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'modality' => ['required', 'string', 'in:online,in_person'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'day_of_week.required' => 'El día de la semana es obligatorio.',
            'day_of_week.between' => 'El día de la semana debe estar entre lunes y domingo.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio no tiene un formato válido.',
            'end_time.required' => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'La hora de fin no tiene un formato válido.',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'modality.required' => 'La modalidad es obligatoria.',
            'modality.in' => 'La modalidad seleccionada no es válida.',
        ];
    }
}
