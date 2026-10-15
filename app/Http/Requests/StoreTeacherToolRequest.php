<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreTeacherToolRequest extends FormRequest
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
            'category' => ['required', 'string', 'in:software,ai'],
            'tool_name' => ['required', 'string', 'max:150', $this->uniqueToolRule()],
            'level' => ['required', 'string', 'in:básico,intermedio,avanzado'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category.required' => 'La categoría es obligatoria.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'tool_name.required' => 'El nombre del programa o herramienta es obligatorio.',
            'tool_name.max' => 'El nombre no puede superar los 150 caracteres.',
            'tool_name.unique' => 'El profesor ya tiene registrado ese programa o herramienta en la categoría seleccionada.',
            'level.required' => 'El nivel es obligatorio.',
            'level.in' => 'El nivel seleccionado no es válido.',
        ];
    }

    private function uniqueToolRule(): Unique
    {
        $rule = Rule::unique('teacher_tools', 'tool_name')->where(
            fn (Builder $query): Builder => $query
                ->where('teacher_id', $this->route('teacher')->id)
                ->where('category', $this->string('category')->toString()),
        );

        if ($teacherTool = $this->route('teacherTool')) {
            $rule->ignore($teacherTool);
        }

        return $rule;
    }
}
