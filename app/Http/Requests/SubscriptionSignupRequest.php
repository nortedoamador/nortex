<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionSignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nome_responsavel' => ['required', 'string', 'max:255'],
            'nome_empresa' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'telefone' => ['required', 'string', 'max:40'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'nome_responsavel' => 'nome',
            'nome_empresa' => 'nome da empresa',
            'email' => 'e-mail',
            'telefone' => 'telefone',
        ];
    }
}
