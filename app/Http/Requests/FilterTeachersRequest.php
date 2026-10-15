<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterTeachersRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(Teacher::STATUSES)],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'modality' => ['nullable', 'string', 'in:in_person,online'],
            'creates_content' => ['nullable', 'string', 'in:0,1'],
            'updates_content' => ['nullable', 'string', 'in:0,1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('q')) {
            return;
        }

        $query = preg_replace('/\s+/u', ' ', trim((string) $this->input('q')));

        $this->merge(['q' => $query !== '' ? $query : null]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'q.max' => 'La búsqueda no puede superar los 255 caracteres.',
            'status.in' => 'El estado seleccionado no es válido.',
            'subject_id.exists' => 'La materia seleccionada no es válida.',
            'modality.in' => 'La modalidad seleccionada no es válida.',
            'creates_content.in' => 'El filtro de creación de contenidos no es válido.',
            'updates_content.in' => 'El filtro de actualización de contenidos no es válido.',
        ];
    }
}
