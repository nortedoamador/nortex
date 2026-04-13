<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class DocumentoModeloFicheiroUpload
{
    /**
     * @return array{error: string}|array{content: string}
     */
    public static function lerConteudoValidado(UploadedFile $arquivo): array
    {
        $nomeOriginal = strtolower($arquivo->getClientOriginalName());
        if (! preg_match('/\.(blade\.php|blade|php|html|htm|txt)$/i', $nomeOriginal)) {
            return ['error' => __('Formato não suportado. Envie um ficheiro .blade.php, .html, .htm ou .txt.')];
        }

        $realPath = $arquivo->getRealPath();
        $conteudo = is_string($realPath) && $realPath !== '' ? file_get_contents($realPath) : false;
        if ($conteudo === false) {
            return ['error' => __('Não foi possível ler o ficheiro enviado.')];
        }
        if (trim($conteudo) === '') {
            return ['error' => __('O ficheiro está vazio.')];
        }

        return ['content' => $conteudo];
    }

    /**
     * Alinha ao mutator de {@see \App\Models\DocumentoModelo::slug()}.
     */
    public static function normalizarSlugCandidato(?string $slug, string $titulo): string
    {
        $raw = filled(trim((string) $slug)) ? trim((string) $slug) : Str::slug($titulo);
        $raw = trim(mb_strtolower(preg_replace('/\s+/', '-', $raw)));

        return $raw;
    }
}
