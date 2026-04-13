<?php

namespace App\Http\Requests;

class UpdateEmbarcacaoRequest extends StoreEmbarcacaoRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('embarcacao'));
    }
}
