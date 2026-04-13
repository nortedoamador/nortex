<?php

namespace App\Http\Requests;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\ProcessoDocumento;
use App\Support\ChaChecklistDocumentoCodigos;
use App\Support\ChecklistDocumentoModelo;
use App\Support\Normam211DocumentoCodigos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateProcessoDocumentoRequest extends FormRequest
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
        return [
            'status' => ['required', Rule::enum(ProcessoDocumentoStatus::class)],
            'data_validade_documento' => ['nullable', 'date'],
            'declaracao_residencia_2g' => ['sometimes', 'boolean'],
            'declaracao_anexo_5h' => ['sometimes', 'boolean'],
            'declaracao_anexo_5d' => ['sometimes', 'boolean'],
            'declaracao_anexo_3d' => ['sometimes', 'boolean'],
            'preenchido_via_modelo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $v = $this->input('data_validade_documento');
        if (is_string($v)) {
            $v = trim($v);
            if ($v !== '' && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    $this->merge(['data_validade_documento' => Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d')]);
                } catch (\Throwable) {
                    // ignora; validação acusará erro
                }
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var ProcessoDocumento|null $doc */
            $doc = $this->route('documento');
            if (! $doc instanceof ProcessoDocumento) {
                return;
            }

            $doc->loadMissing('documentoTipo');
            $codigo = (string) ($doc->documentoTipo?->codigo ?? '');

            if ($this->has('declaracao_residencia_2g')) {
                if ($this->boolean('declaracao_residencia_2g') && $codigo !== Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP) {
                    $validator->errors()->add(
                        'declaracao_residencia_2g',
                        __('A declaração Anexo 2-G só se aplica ao comprovante de residência.'),
                    );
                }

                if ($this->boolean('declaracao_residencia_2g')
                    && $this->input('status') !== ProcessoDocumentoStatus::Enviado->value) {
                    $validator->errors()->add(
                        'declaracao_residencia_2g',
                        __('Para registrar a declaração, o status do item deve ser «Enviado».'),
                    );
                }
            }

            if ($this->has('declaracao_anexo_5h')) {
                if ($this->boolean('declaracao_anexo_5h') && ! Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigo)) {
                    $validator->errors()->add(
                        'declaracao_anexo_5h',
                        __('O modelo Anexo 5-H só se aplica ao requerimento 5-H da NORMAM 211.'),
                    );
                }

                if ($this->boolean('declaracao_anexo_5h')
                    && $this->input('status') !== ProcessoDocumentoStatus::Enviado->value) {
                    $validator->errors()->add(
                        'declaracao_anexo_5h',
                        __('Para registrar o preenchimento do modelo, o status do item deve ser «Enviado».'),
                    );
                }
            }

            if ($this->has('declaracao_anexo_5d')) {
                if ($this->boolean('declaracao_anexo_5d') && ! Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigo)) {
                    $validator->errors()->add(
                        'declaracao_anexo_5d',
                        __('O modelo Anexo 5-D só se aplica à declaração de extravio/dano da CHA (NORMAM 211).'),
                    );
                }

                if ($this->boolean('declaracao_anexo_5d')
                    && $this->input('status') !== ProcessoDocumentoStatus::Enviado->value) {
                    $validator->errors()->add(
                        'declaracao_anexo_5d',
                        __('Para registrar o preenchimento do modelo, o status do item deve ser «Enviado».'),
                    );
                }
            }

            if ($this->has('declaracao_anexo_3d')) {
                if ($this->boolean('declaracao_anexo_3d') && ! Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigo)) {
                    $validator->errors()->add(
                        'declaracao_anexo_3d',
                        __('O modelo Anexo 3-D só se aplica à declaração de extravio CHA-MTA (NORMAM 212).'),
                    );
                }

                if ($this->boolean('declaracao_anexo_3d')
                    && $this->input('status') !== ProcessoDocumentoStatus::Enviado->value) {
                    $validator->errors()->add(
                        'declaracao_anexo_3d',
                        __('Para registrar o preenchimento do modelo, o status do item deve ser «Enviado».'),
                    );
                }
            }

            if ($this->has('preenchido_via_modelo')) {
                if ($this->boolean('preenchido_via_modelo') && ! ChecklistDocumentoModelo::tipoTemModelo($doc->documentoTipo)) {
                    $validator->errors()->add(
                        'preenchido_via_modelo',
                        __('Este item não tem modelo de documento associado.'),
                    );
                }

                if ($this->boolean('preenchido_via_modelo')
                    && $this->input('status') !== ProcessoDocumentoStatus::Enviado->value) {
                    $validator->errors()->add(
                        'preenchido_via_modelo',
                        __('Para registrar o preenchimento do modelo, o status do item deve ser «Enviado».'),
                    );
                }
            }

            if ($this->has('data_validade_documento') && ! ChaChecklistDocumentoCodigos::isCnhComValidade($codigo)) {
                $validator->errors()->add(
                    'data_validade_documento',
                    __('A data de validade só se aplica ao item de CNH (CHA).'),
                );
            }
        });
    }
}
