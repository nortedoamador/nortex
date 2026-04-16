<?php

namespace App\Http\Requests;

use App\Enums\ProcessoStatus;
use App\Models\Processo;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProcessoProvaMarinhaRequest extends FormRequest
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
            'marinha_prova_data' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $v = $this->input('marinha_prova_data');
        if ($v === '' || $v === null) {
            $this->merge(['marinha_prova_data' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Processo|null $processo */
            $processo = $this->route('processo');
            if ($processo && $processo->status !== ProcessoStatus::AguardandoProva) {
                $validator->errors()->add(
                    'marinha_prova_data',
                    __('A data da prova só pode ser definida enquanto o processo está em «Aguardando prova».'),
                );
            }
        });
    }
}
