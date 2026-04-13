<?php

namespace App\Enums;

enum AnexoValidacaoStatus: string
{
    case Pendente = 'pendente';
    case Ok = 'ok';
    case Alerta = 'alerta';
    case Falhou = 'falhou';
    case Ignorado = 'ignorado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Validação pendente',
            self::Ok => 'Validação OK',
            self::Alerta => 'Alerta',
            self::Falhou => 'Reprovado na validação',
            self::Ignorado => 'Validação ignorada',
        };
    }
}
