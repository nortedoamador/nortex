<?php

namespace App\Support;

use App\Models\Cliente;
use Illuminate\Support\Collection;

final class ClienteCpfSuggest
{
    /**
     * @param  Collection<int, Cliente>  $clientes
     * @return Collection<int, array{id: int, hashid: string, doc: string, docDigits: string, nome: string}>
     */
    public static function collection(Collection $clientes): Collection
    {
        return $clientes
            ->filter(fn (Cliente $c) => filled($c->cpf))
            ->values()
            ->map(fn (Cliente $c) => [
                'id' => $c->id,
                'hashid' => $c->getRouteKey(),
                'doc' => $c->documentoFormatado() ?? $c->cpf,
                'docDigits' => preg_replace('/\D/', '', (string) $c->cpf),
                'nome' => $c->nome,
            ]);
    }
}
