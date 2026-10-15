<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherSubjectRequest extends FormRequest
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
            'subject_id' => [
                'nullable',
                'integer',
                'required_without:subject_name',
                Rule::prohibitedIf(fn (): bool => $this->filled('subject_name')),
                'exists:subjects,id',
                Rule::unique('subject_teacher', 'subject_id')->where(
                    fn (Builder $query): Builder => $query->where(
                        'teacher_id',
                        $this->route('teacher')->id,
                    ),
                ),
            ],
            'subject_name' => [
                'nullable',
                'string',
                'max:150',
                'required_without:subject_id',
                Rule::prohibitedIf(fn (): bool => $this->filled('subject_id')),
                Rule::unique('subjects', 'name'),
            ],
            'subject_description' => [
                'nullable',
                'string',
                Rule::prohibitedIf(fn (): bool => $this->filled('subject_id')),
            ],
            'details' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'subject_id.required_without' => 'Selecciona una materia o escribe una nueva.',
            'subject_id.prohibited' => 'No puedes seleccionar y crear una materia a la vez.',
            'subject_id.unique' => 'El profesor ya tiene asociada esa materia.',
            'subject_name.required_without' => 'Selecciona una materia o escribe una nueva.',
            'subject_name.prohibited' => 'No puedes seleccionar y crear una materia a la vez.',
            'subject_name.unique' => 'Ya existe una materia con ese nombre; selecciónala en la lista.',
            'subject_description.prohibited' => 'La descripción solo puede indicarse al crear una materia.',
        ];
    }
}
