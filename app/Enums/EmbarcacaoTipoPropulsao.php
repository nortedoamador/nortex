<?php

namespace App\Enums;

enum EmbarcacaoTipoPropulsao: string
{
    case Motor = 'motor';
    case Vela = 'vela';
    case VelaMotor = 'vela_motor';
    case SemPropulsao = 'sem_propulsao';

    public function label(): string
    {
        return match ($this) {
            self::Motor => __('Motor'),
            self::Vela => __('Vela'),
            self::VelaMotor => __('Vela / motor'),
            self::SemPropulsao => __('Sem propulsão'),
        };
    }

    public function incluiMotor(): bool
    {
        return match ($this) {
            self::Motor, self::VelaMotor => true,
            self::Vela, self::SemPropulsao => false,
        };
    }
}
