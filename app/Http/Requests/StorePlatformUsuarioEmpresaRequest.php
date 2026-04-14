<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StorePlatformUsuarioEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_platform_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $empresaId = (int) $this->input('empresa_id', 0);

        return [
            'empresa_id' => ['required', 'integer', Rule::exists('empresas', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'enviar_convite' => ['sometimes', 'boolean'],
            'password' => [
                Rule::excludeIf(fn () => $this->boolean('enviar_convite')),
                'required',
                'confirmed',
                Password::defaults(),
            ],
            'password_confirmation' => Rule::excludeIf(fn () => $this->boolean('enviar_convite')),
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'integer',
                Rule::exists('roles', 'id')->where('empresa_id', $empresaId),
            ],
        ];
    }
}
