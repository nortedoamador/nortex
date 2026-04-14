<?php

namespace App\Enums;

enum TipoProcessoCategoria: string
{
    case Cha = 'cha';
    case Embarcacao = 'embarcacao';
    case Cir = 'cir';

    public function label(): string
    {
        return match ($this) {
            self::Cha => __('Carteira de Habilitação de Amador (CHA)'),
            self::Embarcacao => __('Embarcação (Capitanias, Delegacias e Agências)'),
            self::Cir => __('Caderneta de Inscrição e Registro (CIR)'),
        };
    }
}
