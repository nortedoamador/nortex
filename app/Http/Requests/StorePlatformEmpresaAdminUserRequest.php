<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\TenantEmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StorePlatformEmpresaAdminUserRequest extends FormRequest
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
        $empresa = TenantEmpresaContext::routeEmpresa($this);
        abort_unless($empresa !== null, 404);

        return [
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
        ];
    }
}
