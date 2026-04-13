<?php

namespace App\Support;

/**
 * Extrai nomes de variáveis em echos Blade simples (`{{ $x`) do HTML do modelo.
 */
final class DocumentoModeloTemplateBladeScan
{
    /** @var list<string> */
    private const IGNORE_VARS = [
        'cliente', 'hoje', 'empresa', 'embarcacao', 'loop', 'errors', 'message', 'slot', 'component',
        '__env', 'app', 'auth', 'request',
    ];

    /**
     * @return array{variaveis: list<array{nome: string, em_normam: bool}>, orfas: list<string>}
     */
    public static function analisar(string $conteudo): array
    {
        preg_match_all('/\{\{\s*\$(\w+)/', $conteudo, $m);
        $nomes = array_values(array_unique($m[1] ?? []));
        sort($nomes);
        $known = array_flip(Normam211212TemplateVars::bladeBindingKeyList());
        $ignore = array_flip(self::IGNORE_VARS);
        $variaveis = [];
        $orfas = [];
        foreach ($nomes as $nome) {
            if (isset($ignore[$nome])) {
                continue;
            }
            $em = isset($known[$nome]);
            $variaveis[] = ['nome' => $nome, 'em_normam' => $em];
            if (! $em) {
                $orfas[] = $nome;
            }
        }

        return ['variaveis' => $variaveis, 'orfas' => $orfas];
    }

    /**
     * Duplica o ficheiro Blade inserindo, após cada echo simples @{{ $variável }}, um comentário Blade com a fonte dos dados (rótulo humano).
     * Serve só para leitura na página de verificação (não executar como modelo).
     */
    public static function anotarComFontesDados(string $conteudo): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*\$(\w+)(\s*\?\?[^}]*)?\s*\}\}/u',
            static function (array $m): string {
                $echo = $m[0];
                $var = $m[1];
                $label = DocumentoModeloVariavelLabels::labelPara($var);
                $hint = preg_replace('/--+/', '—', $label);
                $hint = str_replace('}}', '⟩⟩', $hint);

                return $echo.' {{-- ← '.$hint.' --}}';
            },
            $conteudo
        );
    }
}
