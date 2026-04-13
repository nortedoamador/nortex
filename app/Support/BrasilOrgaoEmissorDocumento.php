<?php

namespace App\Support;

/**
 * Órgão emissor para documento de identidade (RG ou CNH).
 *
 * - RG: SSP/UF + Polícia Federal (legado)
 * - CNH: DETRAN/UF
 *
 * Valor salvo (máx. 32 chars): "SSP/XX" | "DETRAN/XX" | "POLICIA_FEDERAL"
 */
final class BrasilOrgaoEmissorDocumento
{
    public const TIPO_RG = 'rg';
    public const TIPO_CNH = 'cnh';
    public const TIPO_CIN = 'cin';

    /**
     * @return array<string, string>
     */
    public static function optionsPara(string $tipo): array
    {
        return match ($tipo) {
            self::TIPO_CNH => self::optionsCnh(),
            default => self::optionsRg(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function optionsRg(): array
    {
        return BrasilOrgaoEmissorRg::options();
    }

    /**
     * @return array<string, string>
     */
    public static function optionsCnh(): array
    {
        $opts = [];
        foreach (BrasilEstados::options() as $sigla => $nome) {
            $v = 'DETRAN/'.$sigla;
            $opts[$v] = 'DETRAN/'.$sigla.' — '.$nome;
        }

        return $opts;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsAll(): array
    {
        // RG + CNH
        return self::optionsRg() + self::optionsCnh();
    }

    /**
     * @return list<string>
     */
    public static function valoresPermitidosPara(string $tipo): array
    {
        return array_keys(self::optionsPara($tipo));
    }
}

