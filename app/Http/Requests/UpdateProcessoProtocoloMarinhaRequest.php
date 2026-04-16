<?php

namespace App\Http\Requests;

use App\Models\Processo;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProcessoProtocoloMarinhaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Processo $processo */
        $processo = $this->route('processo');

        return $this->user() !== null && $this->user()->can('updateDocumento', $processo);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'marinha_protocolo_numero' => ['required', 'string', 'max:255'],
            'marinha_protocolo_data' => ['required', 'date'],
            'marinha_protocolo_anexo' => ['nullable', 'file', 'max:15360', 'mimes:pdf,jpg,jpeg,png,webp'],
            'remover_marinha_protocolo_anexo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $v = $this->input('remover_marinha_protocolo_anexo');
        if ($v === '1' || $v === 'true' || $v === 1 || $v === true) {
            $this->merge(['remover_marinha_protocolo_anexo' => true]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Processo|null $processo */
            $processo = $this->route('processo');
            if ($processo && ! $processo->status->exigeDadosProtocoloMarinha()) {
                $validator->errors()->add(
                    'marinha_protocolo_numero',
                    __('Nesta etapa não é obrigatório registar o protocolo da Marinha.'),
                );
            }
        });
    }
}
