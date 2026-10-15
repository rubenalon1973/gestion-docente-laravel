<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTeacherDocumentRequest extends FormRequest
{
    /** @var array<string, array<string, list<string>>> */
    private const ALLOWED_FILES = [
        'cv' => [
            'pdf' => ['application/pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ],
        'photo' => [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
        ],
        'qualification' => [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
        ],
        'other' => [
            'pdf' => ['application/pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
        ],
    ];

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
            'category' => ['required', 'string', Rule::in(array_keys(self::ALLOWED_FILES))],
            'document' => ['required', 'file', 'max:10240'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['category', 'document'])) {
                    return;
                }

                $file = $this->file('document');

                if (! $file instanceof UploadedFile) {
                    return;
                }

                $category = $this->string('category')->toString();
                $extension = Str::lower($file->getClientOriginalExtension());
                $mimeType = $file->getMimeType();
                $allowedMimeTypes = self::ALLOWED_FILES[$category][$extension] ?? [];

                if (! in_array($mimeType, $allowedMimeTypes, true)) {
                    $validator->errors()->add(
                        'document',
                        'La extensión, el tipo real del archivo y la categoría seleccionada no son compatibles.',
                    );
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category.required' => 'La categoría es obligatoria.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'document.required' => 'Debes seleccionar un archivo.',
            'document.file' => 'La subida debe contener un archivo válido.',
            'document.max' => 'El archivo no puede superar los 10 MB.',
        ];
    }
}
