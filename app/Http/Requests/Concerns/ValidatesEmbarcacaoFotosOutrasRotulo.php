<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

trait ValidatesEmbarcacaoFotosOutrasRotulo
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if (! $this->hasValidFotosOutrasFiles()) {
                return;
            }
            if (trim((string) $this->input('fotos_outras_rotulo', '')) === '') {
                $v->errors()->add(
                    'fotos_outras_rotulo',
                    __('Descreva o conteúdo destas fotografias (obrigatório para "outras").')
                );
            }
        });
    }

    private function hasValidFotosOutrasFiles(): bool
    {
        $files = $this->file('fotos_outras', []);
        if (! is_array($files)) {
            return false;
        }
        foreach ($files as $f) {
            if ($f instanceof UploadedFile && $f->isValid()) {
                return true;
            }
        }

        return false;
    }
}
