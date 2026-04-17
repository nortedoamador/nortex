<?php

namespace App\Support;

/**
 * Itens do checklist em que o mesmo tipo de documento pode ter vários ficheiros anexados (ex.: fotos).
 */
final class ChecklistDocumentoMultiplosAnexos
{
    /** @var list<string> */
    private const CODIGOS = [
        'FOTOS_POPA_TRAVES',
        'TIE_FOTOS_EMBARCACAO_LATERAL_POPA',
        'TIE_FOTOS_MOTO_AQUATICA',
        Normam211DocumentoCodigos::TIE_DUAS_FOTOS_EMBARCACAO,
    ];

    public static function permite(?string $codigoTipo): bool
    {
        if ($codigoTipo === null || $codigoTipo === '') {
            return false;
        }

        return in_array($codigoTipo, self::CODIGOS, true);
    }
}
