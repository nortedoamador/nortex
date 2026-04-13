<?php

namespace App\Support;

/**
 * Opções de órgão emissor no padrão curto (máx. 32 caracteres) para RG/CNH.
 *
 * @return array<string, string> valor salvo => rótulo
 */
final class BrasilOrgaoEmissorRg
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $opts = [];
        foreach (BrasilEstados::options() as $sigla => $nome) {
            $v = 'SSP/'.$sigla;
            $opts[$v] = 'SSP/'.$sigla.' — '.$nome;
        }
        $opts['POLICIA_FEDERAL'] = 'Polícia Federal';

        return $opts;
    }

    /**
     * @return list<string>
     */
    public static function valoresPermitidos(): array
    {
        return array_keys(self::options());
    }
}
