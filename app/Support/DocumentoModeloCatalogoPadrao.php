<?php

namespace App\Support;

/**
 * Definições legadas dos modelos PDF em ficheiros sob resources/views/documento-modelos/defaults/.
 *
 * @phpstan-type Definicao array{titulo: string, relativePath: string, referencia?: string}
 */
final class DocumentoModeloCatalogoPadrao
{
    /**
     * @return array<string, Definicao>
     */
    public static function mapaFicheirosRelativos(): array
    {
        return [
            'anexo-2g' => [
                'titulo' => 'ANEXO 2-G - Declaração de residência',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2g.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-5h' => [
                'titulo' => 'ANEXO 5-H - Requerimento (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-5h.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-5d' => [
                'titulo' => 'ANEXO 5-D - Declaração de extravio/dano (CHA, NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-5d.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-1c-normam212' => [
                'titulo' => 'ANEXO 1-C - Declaração de residência (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-1c-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2a-normam212' => [
                'titulo' => 'ANEXO 2-A - Requerimento (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2a-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2b-bdmoto-normam212' => [
                'titulo' => 'ANEXO 2-B - BDMOTO (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2b-bdmoto-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2c-normam212' => [
                'titulo' => 'ANEXO 2-C - Declaração de perda/roubo/extravio de TIE (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2c-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-3a-cha-mta-normam212' => [
                'titulo' => 'ANEXO 3-A - Requerimento CHA-MTA (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-3a-cha-mta-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-3d-extravio-cha-mta-normam212' => [
                'titulo' => 'ANEXO 3-D - Declaração de extravio CHA-MTA (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-3d-extravio-cha-mta-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2b-bsade' => [
                'titulo' => 'ANEXO 2-B - BSADE (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2b-bsade.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-2c-normam211' => [
                'titulo' => 'ANEXO 2-C - Requerimento (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2c-normam211.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-2h-normam211' => [
                'titulo' => 'ANEXO 2-H - Declaração de extravio (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2h-normam211.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-2a-normam211' => [
                'titulo' => 'ANEXO 2-A - BADE/BSADE (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2a-normam211.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-2e-normam201' => [
                'titulo' => 'ANEXO 2-E - Requerimento (NORMAM 201)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2e-normam201.blade.php',
                'referencia' => 'NORMAM-201/DPC',
            ],
            'anexo-2b-bade-normam201' => [
                'titulo' => 'ANEXO 2-B - BADE (NORMAM 201)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2b-bade-normam201.blade.php',
                'referencia' => 'NORMAM-201/DPC',
            ],
            'anexo-2p-normam201' => [
                'titulo' => 'ANEXO 2-P - Declaração de residência (NORMAM 201)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2p-normam201.blade.php',
                'referencia' => 'NORMAM-201/DPC',
            ],
            'anexo-2f-normam202' => [
                'titulo' => 'ANEXO 2-F - Requerimento (NORMAM 202)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2f-normam202.blade.php',
                'referencia' => 'NORMAM-202/DPC',
            ],
            'anexo-2e-normam202' => [
                'titulo' => 'ANEXO 2-E - BADE/BSADE (NORMAM 202)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2e-normam202.blade.php',
                'referencia' => 'NORMAM-202/DPC',
            ],
            'anexo-2b-bade-normam202' => [
                'titulo' => 'ANEXO 2-B - BADE (NORMAM 202)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2b-bade-normam202.blade.php',
                'referencia' => 'NORMAM-202/DPC',
            ],
            'anexo-2p-normam202' => [
                'titulo' => 'ANEXO 2-P - Declaração de residência (NORMAM 202)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2p-normam202.blade.php',
                'referencia' => 'NORMAM-202/DPC',
            ],
        ];
    }

    /**
     * @return array<string, Definicao>|null
     */
    public static function metaPorSlug(string $slug): ?array
    {
        $slug = trim(mb_strtolower(preg_replace('/\s+/', '-', $slug)));
        $map = self::mapaFicheirosRelativos();

        return $map[$slug] ?? null;
    }

    public static function conteudoDoFicheiroPadrao(string $slug): ?string
    {
        $meta = self::metaPorSlug($slug);
        if ($meta === null) {
            return null;
        }
        $path = resource_path($meta['relativePath']);
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }
        $raw = file_get_contents($path);

        return is_string($raw) && trim($raw) !== '' ? $raw : null;
    }
}
