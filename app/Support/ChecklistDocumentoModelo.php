<?php

namespace App\Support;

use App\Models\DocumentoTipo;
use App\Models\ProcessoDocumento;

/**
 * Itens do checklist com slug de modelo (coluna ou {@see DocumentoTipo::modeloSlugParaRender()}) podem ser satisfeitos com o PDF do modelo (sem anexo),
 * tal como Anexo 2-G e 5-H.
 */
final class ChecklistDocumentoModelo
{
    public static function tipoTemModelo(?DocumentoTipo $t): bool
    {
        return $t !== null && $t->modeloSlugParaRender() !== '';
    }

    public static function urlPrecisaContextoEmbarcacao(string $slugModelo): bool
    {
        return $slugModelo !== ''
            && str_starts_with($slugModelo, 'anexo-2')
            && $slugModelo !== 'anexo-2g';
    }

    public static function satisfeitoViaModeloOuDeclaracaoLegada(ProcessoDocumento $linha): bool
    {
        $codigo = (string) ($linha->documentoTipo?->codigo ?? '');

        if ($codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP
            && (bool) ($linha->declaracao_residencia_2g ?? false)) {
            return true;
        }

        if (Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigo)
            && (bool) ($linha->declaracao_anexo_5h ?? false)) {
            return true;
        }

        if (Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigo)
            && (bool) ($linha->declaracao_anexo_5d ?? false)) {
            return true;
        }

        if (Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigo)
            && (bool) ($linha->declaracao_anexo_3d ?? false)) {
            return true;
        }

        if (! self::tipoTemModelo($linha->documentoTipo)) {
            return (bool) ($linha->satisfeito_via_ficha_embarcacao ?? false);
        }

        return (bool) ($linha->preenchido_via_modelo ?? false);
    }
}
