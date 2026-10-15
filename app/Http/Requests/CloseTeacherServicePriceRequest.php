<?php

namespace App\Http\Requests;

use App\Models\TeacherServicePrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CloseTeacherServicePriceRequest extends FormRequest
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
        $price = $this->route('teacherServicePrice');
        $validFrom = $price instanceof TeacherServicePrice
            ? $price->valid_from->toDateString()
            : today()->toDateString();

        return [
            'valid_until' => ['required', 'date', 'after_or_equal:'.$validFrom],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $price = $this->route('teacherServicePrice');

                if ($price instanceof TeacherServicePrice && $price->valid_until?->isBefore(today())) {
                    $validator->errors()->add('valid_until', 'No se puede cerrar una tarifa que ya es histórica.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'valid_until.required' => 'Debes indicar la última fecha de vigencia.',
            'valid_until.date' => 'La última fecha de vigencia no es válida.',
            'valid_until.after_or_equal' => 'La última fecha de vigencia no puede ser anterior a la fecha de inicio.',
        ];
    }
}
