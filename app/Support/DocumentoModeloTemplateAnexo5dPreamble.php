<?php

namespace App\Support;

/**
 * No upload (ou gravação do editor), antecede o HTML do ANEXO 5-D (declaração extravio CHA)
 * com o partial que define variáveis derivadas ($rg_dia, $cha_emissao_*, $decl_*, …).
 *
 * @see \App\Support\DocumentoModeloTemplateSpanBinder
 */
final class DocumentoModeloTemplateAnexo5dPreamble
{
    private const INCLUDE_LINE = "@include('documento-modelos.partials.anexo-5d-variaveis')\n";

    public static function prependSeNecessario(string $html): string
    {
        if (self::jaTemPreambulo5d($html)) {
            return $html;
        }
        if (! self::pareceHtmlAnexo5dDeclCha($html)) {
            return $html;
        }

        $pos = stripos($html, '<!DOCTYPE');
        if ($pos !== false) {
            return substr($html, 0, $pos).self::INCLUDE_LINE.substr($html, $pos);
        }

        return self::INCLUDE_LINE.$html;
    }

    public static function jaTemPreambulo5d(string $html): bool
    {
        if (str_contains($html, 'anexo-5d-variaveis')) {
            return true;
        }
        if (str_contains($html, 'nx_anexo_5d_var_preamble')) {
            return true;
        }
        if (preg_match('/\$cha_dt_emissao_fmt\s*=\s*\$cha_dt_emissao_fmt\s*\?\?/', $html) === 1) {
            return true;
        }

        return false;
    }

    public static function pareceHtmlAnexo5dDeclCha(string $html): bool
    {
        $l = strtolower($html);
        if (! str_contains($l, 'anexo 5-d')) {
            return false;
        }
        if (str_contains($l, 'extravio')) {
            return true;
        }
        if (str_contains($l, 'declaração') && str_contains($l, 'cha')) {
            return true;
        }
        if (str_contains($l, 'declaracao') && str_contains($l, 'cha')) {
            return true;
        }

        return false;
    }
}
