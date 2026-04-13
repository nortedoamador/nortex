<?php

namespace App\Support;

/**
 * Injeta o partial {@see resources/views/documento-modelos/partials/nx-pdf24-impressao-a4.blade.php}
 * em modelos exportados pelo PDF24 (criação/upload ou gravação do editor), para impressão A4 preenchida
 * e tipografia de ecrã alinhada ao padrão NORTEX.
 */
final class DocumentoModeloTemplatePdf24ImpressaoA4
{
    private const INCLUDE_LINE = "@include('documento-modelos.partials.nx-pdf24-impressao-a4')\n";

    private const MARKER_PATH = 'nx-pdf24-impressao-a4';

    public static function injectarSeNecessario(string $html): string
    {
        if (str_contains($html, self::MARKER_PATH)) {
            return $html;
        }
        if (! self::pareceExportPdf24($html)) {
            return $html;
        }

        if (preg_match('/<\/head>/i', $html, $m, PREG_OFFSET_CAPTURE) === 1) {
            $pos = $m[0][1];

            return substr($html, 0, $pos)."\n\t".self::INCLUDE_LINE.substr($html, $pos);
        }

        return $html;
    }

    private static function pareceExportPdf24(string $html): bool
    {
        if (! str_contains($html, 'pdf24_02')) {
            return false;
        }

        return str_contains($html, 'pdf24_view')
            || str_contains($html, 'pdf24_05')
            || str_contains($html, 'pdf24_01');
    }
}
