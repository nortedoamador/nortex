<?php

namespace App\Support;

final class HabilitacaoAnexoTiposCha
{
    public const DIGITAL = 'CHA_DIGITAL';

    public const MODELO_ANTIGO = 'CHA_MODELO_ANTIGO';

    /** @return list<string> */
    public static function codigos(): array
    {
        return [self::DIGITAL, self::MODELO_ANTIGO];
    }

    /** @return array<string, string> valor armazenado => rótulo */
    public static function opcoes(): array
    {
        return [
            self::DIGITAL => self::label(self::DIGITAL),
            self::MODELO_ANTIGO => self::label(self::MODELO_ANTIGO),
        ];
    }

    public static function label(?string $codigo): string
    {
        return match ($codigo) {
            self::DIGITAL => __('CHA (DIGITAL)'),
            self::MODELO_ANTIGO => __('CHA (MODELO ANTIGO)'),
            null, '' => __('Sem tipo'),
            default => (string) $codigo,
        };
    }
}
