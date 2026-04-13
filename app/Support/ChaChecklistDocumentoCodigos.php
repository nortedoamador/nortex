<?php

namespace App\Support;

/**
 * Códigos estáveis dos itens de checklist CHA (Carteira de Habilitação de Amador).
 */
final class ChaChecklistDocumentoCodigos
{
    public const CNH_COM_VALIDADE = 'CHA_CNH_COM_VALIDADE';

    public const ATESTADO_MEDICO_PSICOFISICO = 'CHA_ATESTADO_MEDICO_PSICOFISICO';

    public static function isCnhComValidade(?string $codigo): bool
    {
        return strtoupper((string) $codigo) === self::CNH_COM_VALIDADE;
    }

    public static function isAtestadoMedicoPsicofisico(?string $codigo): bool
    {
        return strtoupper((string) $codigo) === self::ATESTADO_MEDICO_PSICOFISICO;
    }
}
