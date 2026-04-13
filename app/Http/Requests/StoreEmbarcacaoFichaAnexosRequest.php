<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreEmbarcacaoFichaAnexosRequest extends StoreMultiplosAnexosRequest
{
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
        $rules = parent::rules();
        $rules['tipo_codigo_preset'] = ['nullable', 'string', Rule::in(['', '__outro', \App\Support\EmbarcacaoTiposAnexo::TIE, \App\Support\EmbarcacaoTiposAnexo::SEGURO_DPEM])];
        $rules['tipo_codigo_custom'] = [
            'nullable',
            'string',
            'max:64',
            Rule::requiredIf(fn (): bool => $this->input('tipo_codigo_preset') === '__outro'),
        ];

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'tipo_codigo_custom.required' => __('Descreva o tipo do anexo.'),
        ]);
    }
}
