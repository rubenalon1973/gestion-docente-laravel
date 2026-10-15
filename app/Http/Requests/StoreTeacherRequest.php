<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_names' => ['required', 'string', 'max:150'],
            'identity_document' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(Teacher::class, 'identity_document')->ignore($this->route('teacher')),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:150'],
            'locality' => ['nullable', 'string', 'max:150'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'province' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'years_of_experience' => ['nullable', 'integer', 'between:0,100'],
            'creates_content' => ['boolean'],
            'updates_content' => ['boolean'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_names.required' => 'Los apellidos son obligatorios.',
            'identity_document.unique' => 'Ya existe un profesor con ese DNI/NIE.',
            'identity_document.max' => 'El DNI/NIE no puede superar los 20 caracteres.',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'postal_code.max' => 'El código postal no puede superar los 32 caracteres.',
            'province.max' => 'La provincia no puede superar los 150 caracteres.',
            'country.max' => 'El país no puede superar los 150 caracteres.',
            'years_of_experience.between' => 'Los años de experiencia deben estar entre 0 y 100.',
            'internal_notes.max' => 'Las observaciones internas no pueden superar los 5.000 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'identity_document' => $this->normalizedIdentityDocument(),
            'creates_content' => $this->boolean('creates_content'),
            'updates_content' => $this->boolean('updates_content'),
        ]);
    }

    private function normalizedIdentityDocument(): ?string
    {
        $identityDocument = preg_replace('/\s+/u', '', (string) $this->input('identity_document'));

        return $identityDocument === '' ? null : mb_strtoupper($identityDocument);
    }
}
