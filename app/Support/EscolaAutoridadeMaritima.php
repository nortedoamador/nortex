<?php

namespace App\Support;

/**
 * Listas fechadas para o representante da autoridade marítima no perfil da escola.
 */
final class EscolaAutoridadeMaritima
{
    /** @var list<string> */
    public const FUNCOES = [
        'Capitão dos Portos',
        'Delegado da Capitania dos Portos',
        'Agente da Capitania dos Portos',
    ];

    /** @var list<string> */
    public const POSTOS = [
        'Capitão-Tenente',
        'Capitão de Corveta',
        'Capitão de Fragata',
        'Capitão de Mar e Guerra',
    ];
}
