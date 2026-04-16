<?php

namespace App\Support;

/**
 * Grava o conteúdo do modelo em resources/views/documento-modelos/defaults/{slug}.blade.php
 * (junto aos demais modelos do repositório).
 */
final class DocumentoModeloPadraoFicheiro
{
    public static function slugValidoParaFicheiro(string $slug): bool
    {
        $slug = trim($slug);

        return $slug !== ''
            && strlen($slug) <= 80
            && ! str_contains($slug, '..')
            && ! str_contains($slug, '/')
            && ! str_contains($slug, '\\')
            && preg_match('/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?$/', $slug) === 1;
    }

    public static function caminhoBlade(string $slug): string
    {
        if (! self::slugValidoParaFicheiro($slug)) {
            throw new \InvalidArgumentException('Slug inválido para caminho de ficheiro.');
        }

        return resource_path('views/documento-modelos/defaults/'.$slug.'.blade.php');
    }

    /**
     * @return string|null Mensagem de erro ou null se OK
     */
    public static function gravarSeModeloEmpresaSemGlobal(\App\Models\DocumentoModelo $modelo, string $conteudo): ?string
    {
        if (! $modelo->escreveFicheiroPadraoNoRepositorio()) {
            return null;
        }

        return self::gravar((string) $modelo->slug, $conteudo);
    }

    public static function gravar(string $slug, string $conteudo): ?string
    {
        if (! self::slugValidoParaFicheiro($slug)) {
            return __('O identificador (slug) não permite gravar o ficheiro em disco.');
        }

        $path = self::caminhoBlade($slug);
        $dir = dirname($path);
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return __('Não foi possível criar a pasta de modelos padrão.');
        }

        if (@file_put_contents($path, $conteudo) === false) {
            return __('Não foi possível gravar o ficheiro do modelo em :path.', ['path' => $path]);
        }

        return null;
    }

    /**
     * Remove o ficheiro em resources/views/documento-modelos/defaults/{slug}.blade.php, se existir.
     * Chamado ao excluir o registo do modelo (catálogo NORMAM ou slug próprio).
     * Para repor anexos padrão do repositório, use git ou volte a publicar os ficheiros em defaults/.
     */
    public static function apagarFicheiroBladeSeExistir(string $slug): void
    {
        if (! self::slugValidoParaFicheiro($slug)) {
            return;
        }

        $path = self::caminhoBlade($slug);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
