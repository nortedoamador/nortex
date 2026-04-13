<?php

namespace App\Support;

final class EmbarcacaoTiposAnexo
{
    public const TIE = 'TIE';

    public const SEGURO_DPEM = 'SEGURO_DPEM';

    public const FOTO_TRAVES = 'FOTO_TRAVES';

    public const FOTO_POPA = 'FOTO_POPA';

    /** Fotos adicionais (vários anexos por embarcação). */
    public const FOTO_OUTRAS = 'FOTO_OUTRAS';

    public static function label(?string $codigo): string
    {
        if ($codigo === null || $codigo === '') {
            return '—';
        }

        return match ($codigo) {
            self::TIE => 'TIE',
            self::SEGURO_DPEM => 'Seguro DPEM',
            self::FOTO_TRAVES => __('Través (vista lateral)'),
            self::FOTO_POPA => __('Foto da popa'),
            self::FOTO_OUTRAS => __('Outras fotos da embarcação'),
            default => $codigo,
        };
    }
}
