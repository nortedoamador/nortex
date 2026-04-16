<?php

namespace App\Http\Requests;

use App\Models\Cliente;

/**
 * Mesmas regras e preparação que {@see StoreClienteRequest}, mas permite quem gere aulas/escola
 * criar cliente a partir de modais (ex.: diretor) sem exigir explicitamente clientes.manage.
 */
class StoreClienteModalRequest extends StoreClienteRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->can('create', Cliente::class)
            || $user->hasPermission('aulas.manage');
    }
}
