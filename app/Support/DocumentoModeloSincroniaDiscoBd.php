<?php

namespace App\Support;

use App\Models\DocumentoModelo;

/**
 * Mantém {@see DocumentoModelo::$conteudo} alinhado com resources/views/documento-modelos/defaults/{slug}.blade.php
 * quando o ficheiro em disco é editado fora da aplicação.
 */
final class DocumentoModeloSincroniaDiscoBd
{
    public static function normalizarQuebrasLinha(string $conteudo): string
    {
        return str_replace(["\r\n", "\r"], "\n", $conteudo);
    }

    /**
     * Lê o ficheiro em defaults/{slug}.blade.php quando existir e o slug for válido.
     */
    public static function lerConteudoFicheiroPadrao(string $slug): ?string
    {
        if (! DocumentoModeloPadraoFicheiro::slugValidoParaFicheiro($slug)) {
            return null;
        }
        $path = DocumentoModeloPadraoFicheiro::caminhoBlade($slug);
        if (! is_readable($path)) {
            return null;
        }
        $raw = @file_get_contents($path);

        return is_string($raw) ? $raw : null;
    }

    /**
     * Se o ficheiro em disco divergir da coluna conteudo, actualiza a base de dados.
     *
     * @return bool true se o registo foi actualizado
     */
    public static function aplicar(DocumentoModelo $modelo): bool
    {
        if ($modelo->documento_modelo_global_id !== null) {
            return false;
        }

        $slug = (string) $modelo->getAttribute('slug');
        $noDisco = self::lerConteudoFicheiroPadrao($slug);
        if ($noDisco === null) {
            return false;
        }
        $a = self::normalizarQuebrasLinha($noDisco);
        $b = self::normalizarQuebrasLinha((string) $modelo->getAttribute('conteudo'));
        if ($a === $b) {
            return false;
        }
        $modelo->forceFill([
            'conteudo' => $noDisco,
            'conteudo_upload_bruto' => $noDisco,
            'upload_mapeamento_pendente' => false,
        ])->save();

        return true;
    }
}
