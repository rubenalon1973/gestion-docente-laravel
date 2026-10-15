<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherAvailabilityRequest extends FormRequest
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
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['required', 'integer', 'between:1,7', 'distinct'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'modality' => ['required', 'string', 'in:online,in_person'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'days.required' => 'Debes seleccionar al menos un día de la semana.',
            'days.array' => 'La selección de días no es válida.',
            'days.min' => 'Debes seleccionar al menos un día de la semana.',
            'days.*.required' => 'Cada día seleccionado debe ser válido.',
            'days.*.integer' => 'Cada día seleccionado debe ser válido.',
            'days.*.between' => 'Los días seleccionados deben estar entre lunes y domingo.',
            'days.*.distinct' => 'No puedes seleccionar un día más de una vez.',
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
