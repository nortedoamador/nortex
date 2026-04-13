<?php

namespace App\Http\Requests;

use App\Support\HabilitacaoAnexoTiposCha;
use Illuminate\Validation\Rule;

class StoreHabilitacaoAnexosRequest extends StoreMultiplosAnexosRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['tipo_codigo'] = ['required', 'string', Rule::in(HabilitacaoAnexoTiposCha::codigos())];

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'tipo_codigo.required' => __('Selecione o tipo de anexo da CHA.'),
            'tipo_codigo.in' => __('Tipo de anexo inválido.'),
        ]);
    }
}
