<?php

namespace App\Support;

/**
 * Códigos de tipos de documento da Marinha usados em checklist / declaração gerada.
 * Abrange principalmente NORMAM-211; inclui códigos de moto aquática (NORMAM-212) onde o mesmo ficheiro é usado.
 */
final class Normam211DocumentoCodigos
{
    /** Código legado: residência NORMAM-212 via Anexo 1-C (o identificador mantém «212_2C» por compatibilidade com base existente). */
    public const CHA_COMPROVANTE_RESIDENCIA_212_1C_LEGACY = 'CHA_COMPROVANTE_RESIDENCIA_212_2C';

    /** Requerimento CHA-MTA (NORMAM-212), Anexo 3-A. */
    public const CHA_REQ_ANEXO_3A_212 = 'CHA_REQ_ANEXO_3A_212';

    /** Declaração de extravio CHA-MTA (NORMAM-212), Anexo 3-D. */
    public const CHA_DECL_EXTRAVIO_MTA_3D_212 = 'CHA_DECL_EXTRAVIO_MTA_3D_212';
    public const COMPROVANTE_RESIDENCIA_CEP = 'COMPROVANTE_RESIDENCIA_CEP';

    public const CHA_REQ_ANEXO_5H = 'CHA_REQ_ANEXO_5H';

    public const CHA_REQ_ANEXO_5H_OCORRENCIA = 'CHA_REQ_ANEXO_5H_OCORRENCIA';

    public const CHA_DECL_EXTRAVIO_DANO_ANEXO_5D = 'CHA_DECL_EXTRAVIO_DANO_ANEXO_5D';

    public const TIE_BDMOTO_212_2B = 'TIE_BDMOTO_212_2B';

    public const TIE_BDMOTO_SE_ALTERACAO = 'TIE_BDMOTO_SE_ALTERACAO';

    /** Checklist: identificação do procurador (um único tipo; ex-CHA/CIR/TIE). */
    public const DOCUMENTO_PROCURADOR = 'DOCUMENTO_PROCURADOR';

    /** CNH ou RG (CHA/CIR/tipo «solto» fundidos num único código). */
    public const CNH_OU_RG = 'CNH_OU_RG';

    /** Duas fotos da embarcação (fundido de vários `TIE_XX_DUAS_FOTOS` numerados). */
    public const TIE_DUAS_FOTOS_EMBARCACAO = 'TIE_DUAS_FOTOS_EMBARCACAO';

    /**
     * @return list<string>
     */
    public static function codigosCnhOuRg(): array
    {
        return [
            self::CNH_OU_RG,
            'CHA_CNH_OU_RG',
            'CIR_CNH_VALIDA_OU_RG',
        ];
    }

    public static function isCnhOuRg(?string $codigo): bool
    {
        if ($codigo === null || $codigo === '') {
            return false;
        }

        $u = strtoupper($codigo);

        foreach (self::codigosCnhOuRg() as $c) {
            if (strtoupper($c) === $u) {
                return true;
            }
        }

        return false;
    }

    /**
     * Requerimento 5-H / ocorrência: podem ser satisfeitos com modelo PDF (legado Anexo 2-H decl. extravio) + flags.
     *
     * @return list<string>
     */
    public static function codigosDeclaracaoAnexo5h(): array
    {
        return [
            self::CHA_REQ_ANEXO_5H,
            self::CHA_REQ_ANEXO_5H_OCORRENCIA,
        ];
    }

    public static function isDeclaracaoAnexo5h(?string $codigo): bool
    {
        return $codigo !== null && $codigo !== '' && in_array($codigo, self::codigosDeclaracaoAnexo5h(), true);
    }

    /**
     * Declaração Anexo 5-D (extravio/dano CHA): modelo PDF + flag {@see ProcessoDocumento::$declaracao_anexo_5d}.
     *
     * @return list<string>
     */
    public static function codigosDeclaracaoAnexo5d(): array
    {
        return [
            self::CHA_DECL_EXTRAVIO_DANO_ANEXO_5D,
        ];
    }

    public static function isDeclaracaoAnexo5d(?string $codigo): bool
    {
        return $codigo !== null && $codigo !== '' && in_array($codigo, self::codigosDeclaracaoAnexo5d(), true);
    }

    /**
     * Declaração Anexo 3-D (extravio CHA-MTA, NORMAM-212): modelo PDF + flag {@see ProcessoDocumento::$declaracao_anexo_3d}.
     *
     * @return list<string>
     */
    public static function codigosDeclaracaoAnexo3d(): array
    {
        return [
            self::CHA_DECL_EXTRAVIO_MTA_3D_212,
        ];
    }

    public static function isDeclaracaoAnexo3d(?string $codigo): bool
    {
        return $codigo !== null && $codigo !== '' && in_array($codigo, self::codigosDeclaracaoAnexo3d(), true);
    }

    /**
     * Slug do modelo PDF quando a coluna {@see DocumentoTipo::$modelo_slug} está vazia ou legada.
     */
    public static function slugModeloPorCodigoChecklist(?string $codigo): string
    {
        if ($codigo === null || $codigo === '') {
            return '';
        }
        if ($codigo === self::COMPROVANTE_RESIDENCIA_CEP) {
            return 'anexo-2g';
        }
        if (self::isDeclaracaoAnexo5h($codigo)) {
            return 'anexo-5h';
        }
        if (self::isDeclaracaoAnexo5d($codigo)) {
            return 'anexo-5d';
        }
        if ($codigo === self::TIE_BDMOTO_212_2B || $codigo === self::TIE_BDMOTO_SE_ALTERACAO) {
            return 'anexo-2b-bdmoto-normam212';
        }
        if ($codigo === self::CHA_COMPROVANTE_RESIDENCIA_212_1C_LEGACY) {
            return 'anexo-1c-normam212';
        }
        if ($codigo === self::CHA_REQ_ANEXO_3A_212) {
            return 'anexo-3a-cha-mta-normam212';
        }
        if ($codigo === self::CHA_DECL_EXTRAVIO_MTA_3D_212) {
            return 'anexo-3d-extravio-cha-mta-normam212';
        }

        return '';
    }
}
