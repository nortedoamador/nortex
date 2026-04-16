<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesEmbarcacaoFotosOutrasRotulo;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmbarcacaoFotosCadastroRequest extends FormRequest
{
    use ValidatesEmbarcacaoFotosOutrasRotulo;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('embarcacao'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'foto_traves' => ['nullable', 'file', 'max:'.upload_max_kb(), 'mimes:jpg,jpeg,png,webp'],
            'foto_popa' => ['nullable', 'file', 'max:'.upload_max_kb(), 'mimes:jpg,jpeg,png,webp'],
            'fotos_outras' => ['nullable', 'array', 'max:30'],
            'fotos_outras.*' => ['file', 'max:'.upload_max_kb(), 'mimes:jpg,jpeg,png,webp'],
            'fotos_outras_rotulo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fotos_outras.*.max' => __('Cada imagem deve ter no máximo :max.', ['max' => upload_max_file_help()]),
            'fotos_outras.*.mimes' => __('Use apenas JPG, PNG ou WebP.'),
        ];
    }
}
