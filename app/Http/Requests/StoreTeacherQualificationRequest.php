<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherQualificationRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:200'],
            'institution' => ['nullable', 'string', 'max:200'],
            'completion_year' => ['nullable', 'integer', 'between:1900,'.now()->year],
            'notes' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'El título o estudio es obligatorio.',
            'title.max' => 'El título o estudio no puede superar los 200 caracteres.',
            'institution.max' => 'La institución no puede superar los 200 caracteres.',
            'completion_year.integer' => 'El año de finalización debe ser un número entero.',
            'completion_year.between' => 'El año de finalización debe estar entre 1900 y el año actual.',
        ];
    }
}
