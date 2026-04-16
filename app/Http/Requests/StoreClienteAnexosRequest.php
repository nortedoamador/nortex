<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreClienteAnexosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private function hasArquivos(): bool
    {
        $f = $this->file('arquivos');
        if ($f === null) {
            return false;
        }
        if (is_array($f)) {
            return count(array_filter($f, fn ($file) => $file instanceof UploadedFile)) > 0;
        }

        return $f instanceof UploadedFile;
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
            'arquivos.*' => ['file', 'max:'.upload_max_kb(), 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'tipo_codigo' => [
                Rule::requiredIf(fn (): bool => $this->hasArquivos()),
                'nullable',
                'string',
                'max:64',
            ],
            'platform_anexo_tipo_id' => ['nullable', 'integer', Rule::exists('platform_anexo_tipos', 'id')->where('ativo', true)],
        ];
    }

    public function messages(): array
    {
        return [
            'arquivos.*.max' => __('Cada arquivo deve ter no máximo :max.', ['max' => upload_max_file_help()]),
            'arquivos.*.mimes' => 'Formatos permitidos: PDF, imagens, DOC/DOCX.',
            'tipo_codigo.required' => __('Selecione o tipo do documento (ex.: CNH) antes de enviar os arquivos.'),
        ];
    }
}
