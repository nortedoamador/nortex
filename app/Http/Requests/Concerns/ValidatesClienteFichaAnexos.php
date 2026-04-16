<?php

namespace App\Http\Requests\Concerns;

trait ValidatesClienteFichaAnexos
{
    /**
     * @return array<string, mixed>
     */
    protected function clienteFichaAnexoRules(): array
    {
        $file = ['file', 'max:'.upload_max_kb(), 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'];

        return [
            'anexo_cnh' => ['nullable', 'array', 'max:20'],
            'anexo_cnh.*' => $file,
            'anexo_comprovante' => ['nullable', 'array', 'max:20'],
            'anexo_comprovante.*' => $file,
            'anexo_outro' => ['nullable', 'array', 'max:20'],
            'anexo_outro.*' => $file,
            'anexo_outro_tipo' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function clienteFichaAnexoMessages(): array
    {
        return [
            'anexo_cnh.*.max' => __('Cada arquivo deve ter no máximo :max.', ['max' => upload_max_file_help()]),
            'anexo_cnh.*.mimes' => __('Formatos permitidos: PDF, imagens, DOC/DOCX.'),
            'anexo_comprovante.*.max' => __('Cada arquivo deve ter no máximo :max.', ['max' => upload_max_file_help()]),
            'anexo_comprovante.*.mimes' => __('Formatos permitidos: PDF, imagens, DOC/DOCX.'),
            'anexo_outro.*.max' => __('Cada arquivo deve ter no máximo :max.', ['max' => upload_max_file_help()]),
            'anexo_outro.*.mimes' => __('Formatos permitidos: PDF, imagens, DOC/DOCX.'),
        ];
    }
}
