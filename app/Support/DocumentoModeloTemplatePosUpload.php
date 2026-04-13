<?php

namespace App\Support;

/**
 * Pipeline aplicado ao HTML bruto do upload (spans → Blade, preamble 5-D, CSS impressão PDF24).
 *
 * @see \App\Http\Controllers\DocumentoModeloController::confirmarMapeamentoUpload
 */
final class DocumentoModeloTemplatePosUpload
{
    /**
     * @return array{html: string, mapeamento_upload: array{gerado_em: string, itens: list<array<string, mixed>>}}
     */
    public static function processar(string $raw): array
    {
        $binder = DocumentoModeloTemplateSpanBinder::aplicarComRelatorio($raw);
        $html = DocumentoModeloTemplatePdf24ImpressaoA4::injectarSeNecessario(
            DocumentoModeloTemplateAnexo5dPreamble::prependSeNecessario($binder['html'])
        );

        return [
            'html' => $html,
            'mapeamento_upload' => [
                'gerado_em' => now()->toIso8601String(),
                'itens' => $binder['itens'],
            ],
        ];
    }
}
