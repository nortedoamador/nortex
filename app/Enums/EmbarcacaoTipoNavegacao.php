<?php

namespace App\Enums;

enum EmbarcacaoTipoNavegacao: string
{
    case Interior = 'interior';
    case MarAberto = 'mar_aberto';

    public function label(): string
    {
        return match ($this) {
            self::Interior => __('Interior'),
            self::MarAberto => __('Mar aberto'),
        };
    }

    /**
     * @return list<EmbarcacaoAreaNavegacao>
     */
    public function areasPermitidas(): array
    {
        return match ($this) {
            self::Interior => [EmbarcacaoAreaNavegacao::Interior],
            self::MarAberto => [EmbarcacaoAreaNavegacao::Costeira, EmbarcacaoAreaNavegacao::Oceanica],
        };
    }
}
