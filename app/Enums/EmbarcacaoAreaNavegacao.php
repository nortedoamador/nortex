<?php

namespace App\Enums;

enum EmbarcacaoAreaNavegacao: string
{
    case Interior = 'interior';
    case Costeira = 'costeira';
    case Oceanica = 'oceanica';

    public function label(): string
    {
        return match ($this) {
            self::Interior => __('Interior'),
            self::Costeira => __('Costeira'),
            self::Oceanica => __('Oceânica'),
        };
    }
}
