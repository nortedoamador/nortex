<?php

namespace App\Http\Requests;

use App\Models\ProcessoDocumento;
use App\Support\ChecklistDocumentoMultiplosAnexos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMultiplosAnexosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $doc = $this->route('documento');
        $codigo = '';
        if ($doc instanceof ProcessoDocumento) {
            $doc->loadMissing('documentoTipo');
            $codigo = (string) ($doc->documentoTipo?->codigo ?? '');
        }

        $arquivoRules = ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'];
        if (ChecklistDocumentoMultiplosAnexos::permite($codigo)) {
            $arquivoRules = ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp'];
        }

        return [
            'arquivos' => ['required', 'array', 'min:1', 'max:20'],
            'arquivos.*' => $arquivoRules,
            'tipo_codigo' => ['nullable', 'string', 'max:64'],
            'platform_anexo_tipo_id' => ['nullable', 'integer', Rule::exists('platform_anexo_tipos', 'id')->where('ativo', true)],
        ];
    }

    public function messages(): array
    {
        $doc = $this->route('documento');
        $codigo = '';
        if ($doc instanceof ProcessoDocumento) {
            $doc->loadMissing('documentoTipo');
            $codigo = (string) ($doc->documentoTipo?->codigo ?? '');
        }

        $mimesMsg = ChecklistDocumentoMultiplosAnexos::permite($codigo)
            ? __('Use apenas imagens JPG, PNG ou WebP (até 10 MB por ficheiro).')
            : __('Formatos permitidos: PDF, imagens, DOC/DOCX.');

        return [
            'arquivos.required' => __('Selecione ao menos um arquivo.'),
            'arquivos.*.max' => __('Cada arquivo deve ter no máximo 10 MB.'),
            'arquivos.*.mimes' => $mimesMsg,
        ];
    }
}
