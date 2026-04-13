<?php

namespace App\Support;

final class ClienteTiposAnexo
{
    public const CNH = 'CNH';

    public const COMPROVANTE_ENDERECO = 'COMPROVANTE_ENDERECO';

    public static function label(?string $codigo): string
    {
        return match ($codigo) {
            self::CNH => __('CNH'),
            self::COMPROVANTE_ENDERECO => __('Comprovante de endereço'),
            null, '' => __('Sem tipo'),
            default => $codigo,
        };
    }
}
