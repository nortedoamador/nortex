<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteAnexosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $preset = $this->input('tipo_codigo_preset');
        $custom = $this->input('tipo_codigo_custom');

        if (is_string($preset) || is_string($custom)) {
            $tipo = null;
            if (is_string($preset) && trim($preset) !== '' && trim($preset) !== '__outro') {
                $tipo = trim($preset);
            } elseif (is_string($custom) && trim($custom) !== '') {
                $tipo = trim($custom);
            }
            $this->merge(['tipo_codigo' => $tipo]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'arquivos' => ['nullable', 'array', 'max:20'],
            'arquivos.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'tipo_codigo' => ['nullable', 'string', 'max:64'],
            'platform_anexo_tipo_id' => ['nullable', 'integer', Rule::exists('platform_anexo_tipos', 'id')->where('ativo', true)],
        ];
    }

    public function messages(): array
    {
        return [
            'arquivos.*.max' => 'Cada arquivo deve ter no máximo 10 MB.',
            'arquivos.*.mimes' => 'Formatos permitidos: PDF, imagens, DOC/DOCX.',
        ];
    }
}
