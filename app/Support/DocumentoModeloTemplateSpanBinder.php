<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * No upload ou gravação do editor, liga `<span>` a variáveis NORMAM/cliente/embarcação
 * (ver {@see Normam211212TemplateVars}).
 *
 * 1) Explícito: `data-nx="cpf"` ou `data-campo="cpf"` → interior vira `{{ $cpf ?? '' }}` (mantém atributos)
 * 2) PDF24: span com classe `pdf24_N` ou `pdf24_valor` e texto no formato CPF/CEP/telefone/e-mail/CHA → chave correspondente
 * 3) Heurístico: span só com texto curto igual a um alias → mesma substituição
 *
 * Não usa DOMDocument para não alterar o resto do Blade/HTML exportado (PDF24, etc.).
 */
final class DocumentoModeloTemplateSpanBinder
{
    private const RELATORIO_TEXTO_MAX = 200;

    private const RELATORIO_ATTRS_MAX = 120;

    /**
     * Texto normalizado (minúsculas, sem acento) → chave de variável Blade.
     *
     * @var array<string, string>
     */
    private const ALIAS_TO_KEY = [
        'nome' => 'nome',
        'nome completo' => 'nome',
        'proprietario' => 'nome',
        'proprietário' => 'nome',
        'titular' => 'nome',
        'cpf' => 'cpf',
        'c.p.f' => 'cpf',
        'c p f' => 'cpf',
        'c.p.f.' => 'cpf',
        'rg' => 'rg',
        'identidade' => 'identidade',
        'documento de identidade' => 'identidade',
        'orgao expedidor' => 'orgao',
        'orgao' => 'orgao',
        'orgão expedidor' => 'orgao',
        'orgão' => 'orgao',
        'endereco' => 'endereco',
        'endereço' => 'endereco',
        'rua' => 'endereco',
        'logradouro' => 'endereco',
        'numero' => 'numero',
        'número' => 'numero',
        'no' => 'numero',
        'nº' => 'numero',
        'complemento' => 'complemento',
        'apto' => 'apartamento',
        'apartamento' => 'apartamento',
        'bairro' => 'bairro',
        'cidade' => 'cidade',
        'municipio' => 'cidade',
        'município' => 'cidade',
        'uf' => 'uf',
        'estado' => 'uf',
        'cep' => 'cep',
        'telefone' => 'telefone',
        'tel' => 'telefone',
        'fone' => 'telefone',
        'celular' => 'celular',
        'email' => 'email',
        'e-mail' => 'email',
        'fax' => 'fax',
        'nacionalidade' => 'nacionalidade',
        'naturalidade' => 'naturalidade',
        'data de nascimento' => 'nascimento',
        'nascimento' => 'nascimento',
        'data' => 'data',
        'hoje' => 'data',
        'data emissao' => 'dt_emissao',
        'data emissão' => 'dt_emissao',
        'emissao' => 'dt_emissao',
        'emissão' => 'dt_emissao',
        'embarcacao' => 'nome_embarcacao',
        'embarcação' => 'nome_embarcacao',
        'nome da embarcacao' => 'nome_embarcacao',
        'nome da embarcação' => 'nome_embarcacao',
        'nome embarcacao' => 'nome_embarcacao',
        'nome embarcação' => 'nome_embarcacao',
        'nome do veiculo' => 'nome_embarcacao',
        'nome do veículo' => 'nome_embarcacao',
        'inscricao' => 'inscricao',
        'inscrição' => 'inscricao',
        'numero de inscricao' => 'inscricao',
        'número de inscrição' => 'inscricao',
        'numero inscricao' => 'inscricao',
        'número inscrição' => 'inscricao',
        'cha' => 'cha_numero',
        'numero cha' => 'cha_numero',
        'número cha' => 'cha_numero',
        'n cha' => 'cha_numero',
        'tipo' => 'tipo',
        'tipo de embarcacao' => 'tipo',
        'tipo de embarcação' => 'tipo',
        'area de navegacao' => 'area_navegacao',
        'área de navegação' => 'area_navegacao',
        'interior' => 'area_navegacao',
        'construtor' => 'construtor',
        'estaleiro' => 'construtor',
        'ano' => 'ano',
        'ano de construcao' => 'ano',
        'ano de construção' => 'ano',
        'comprimento' => 'comprimento',
        'comprimento loa' => 'comprimento',
        'casco' => 'casco',
        'numero casco' => 'numero_casco',
        'número casco' => 'numero_casco',
        'n casco' => 'numero_casco',
        'classificacao' => 'classificacao',
        'classificação' => 'classificacao',
        'tripulantes' => 'tripulantes',
        'passageiros' => 'passageiros',
        'boca' => 'boca',
        'pontal' => 'pontal',
        'calado' => 'calado',
        'contorno' => 'contorno',
        'material casco' => 'material_casco',
        'material do casco' => 'material_casco',
        'potencia maxima casco' => 'potmax_casco',
        'potência máxima casco' => 'potmax_casco',
        'potencia maxima do casco' => 'potmax_casco',
        'potência máxima do casco' => 'potmax_casco',
        'arqueacao bruta' => 'arq_bruta',
        'arqueação bruta' => 'arq_bruta',
        'arq bruta' => 'arq_bruta',
        'arqueacao liquida' => 'arq_liquida',
        'arqueação líquida' => 'arq_liquida',
        'arq liquida' => 'arq_liquida',
        'marca motor' => 'marca_motor',
        'marca do motor' => 'marca_motor',
        'potencia motor' => 'potmax_motor',
        'potência motor' => 'potmax_motor',
        'pot max' => 'potmax_motor',
        'potência máxima' => 'potmax_motor',
        'numero motor' => 'numero_motor',
        'número motor' => 'numero_motor',
        'n motor' => 'numero_motor',
        'marca motor 2' => 'marca_motor2',
        'marca motor 3' => 'marca_motor3',
        'potencia motor 2' => 'potmax_motor2',
        'potência motor 2' => 'potmax_motor2',
        'potencia motor 3' => 'potmax_motor3',
        'potência motor 3' => 'potmax_motor3',
        'numero motor 2' => 'numero_motor2',
        'número motor 2' => 'numero_motor2',
        'numero motor 3' => 'numero_motor3',
        'número motor 3' => 'numero_motor3',
        'numero nf' => 'numero_nf',
        'número nf' => 'numero_nf',
        'nº nf' => 'numero_nf',
        'nf' => 'numero_nf',
        'nota fiscal' => 'numero_nf',
        'data nf' => 'dt_nf',
        'data da nf' => 'dt_nf',
        'local nf' => 'local_nf',
        'local da nf' => 'local_nf',
        'vendedor' => 'vendedor_nf',
        'vendedor nf' => 'vendedor_nf',
        'documento vendedor' => 'documento_vendedor_nf',
        'doc vendedor' => 'documento_vendedor_nf',
        'endereco completo' => 'endereco_completo',
        'endereço completo' => 'endereco_completo',
        'telefone e-mail' => 'telefone_email_linha',
        'telefone email' => 'telefone_email_linha',
        'ocorrencia' => 'ocorrencia',
        'ocorrência' => 'ocorrencia',
        'observacao' => 'observacao',
        'observação' => 'observacao',
        'obs' => 'observacao',
        'novo nome embarcacao' => 'novo_nome_embarcacao',
        'novo nome embarcação' => 'novo_nome_embarcacao',
        'novo nome' => 'novo_nome_embarcacao',
    ];

    /**
     * @return array{html: string, itens: list<array<string, mixed>>}
     */
    public static function aplicarComRelatorio(string $html): array
    {
        if (trim($html) === '' || ! str_contains($html, '<span')) {
            return ['html' => $html, 'itens' => []];
        }

        $itens = [];
        $allowed = array_fill_keys(Normam211212TemplateVars::bladeBindingKeyList(), true);
        $html = self::aplicarDataCampoExplicito($html, $allowed, $itens);
        $html = self::aplicarPadroesFormatadosEmSpansPdf24($html, $allowed, $itens);
        $html = self::aplicarAliasesEmSpansSimples($html, $allowed, $itens);

        return ['html' => $html, 'itens' => $itens];
    }

    public static function aplicar(string $html): string
    {
        return self::aplicarComRelatorio($html)['html'];
    }

    private static function truncarRelatorio(string $s, int $max = self::RELATORIO_TEXTO_MAX): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
        if (mb_strlen($s) <= $max) {
            return $s;
        }

        return mb_substr($s, 0, $max - 1).'…';
    }

    /**
     * `data-nx` ou `data-campo` com valor permitido.
     *
     * @param  array<string, true>  $allowed
     * @param  list<array<string, mixed>>  $itens
     */
    private static function aplicarDataCampoExplicito(string $html, array $allowed, array &$itens): string
    {
        $out = preg_replace_callback(
            '/<span(\s[^>]*\b(?:data-nx|data-campo)\s*=\s*(["\'])([^"\']+)\2[^>]*)>[\s\S]*?<\/span>/iu',
            static function (array $m) use ($allowed, &$itens): string {
                $key = trim($m[3]);
                if (! isset($allowed[$key])) {
                    return $m[0];
                }
                $inner = self::extrairInteriorSpan($m[0]);
                if ($inner !== null && (str_contains($inner, '{{') || str_contains($inner, '@'))) {
                    return $m[0];
                }
                $origem = preg_match('/\bdata-nx\s*=/i', $m[1]) ? 'data-nx' : (preg_match('/\bdata-campo\s*=/i', $m[1]) ? 'data-campo' : 'explicito');
                $itens[] = [
                    'tipo' => 'explicito',
                    'variavel' => $key,
                    'texto_antes' => self::truncarRelatorio($inner ?? ''),
                    'origem' => $origem,
                    'atributos_span' => self::truncarRelatorio($m[1], self::RELATORIO_ATTRS_MAX),
                ];

                return '<span'.$m[1].'>{{ $'.$key." ?? '' }}</span>";
            },
            $html
        );

        return is_string($out) ? $out : $html;
    }

    /**
     * PDF24: valores já preenchidos no HTML (CPF, CEP, telefone, e-mail, n.º CHA típico) em spans com classe `pdf24_*`.
     *
     * @param  array<string, true>  $allowed
     * @param  list<array<string, mixed>>  $itens
     */
    private static function aplicarPadroesFormatadosEmSpansPdf24(string $html, array $allowed, array &$itens): string
    {
        $out = preg_replace_callback(
            '/<span(\s[^>]*)>([^<]{1,120})<\/span>/iu',
            static function (array $m) use ($allowed, &$itens): string {
                $attrs = $m[1];
                if (! preg_match('/\b(?:pdf24_\d+|pdf24_valor)\b/i', $attrs)) {
                    return $m[0];
                }
                if (preg_match('/\bdata-nx\s*=/i', $attrs) || preg_match('/\bdata-campo\s*=/i', $attrs)) {
                    return $m[0];
                }
                $innerRaw = $m[2];
                if (str_contains($innerRaw, '{{')) {
                    return $m[0];
                }
                $inner = trim(html_entity_decode($innerRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $inner = trim(str_replace("\xc2\xa0", ' ', $inner));
                $inner = preg_replace('/\s+/u', ' ', $inner) ?? $inner;
                if ($inner === '') {
                    return $m[0];
                }
                $pdfClasse = null;
                if (preg_match('/\b(pdf24_(?:\d+|valor))\b/i', $attrs, $cm)) {
                    $pdfClasse = $cm[1];
                }
                if (filter_var($inner, FILTER_VALIDATE_EMAIL) && isset($allowed['email'])) {
                    $itens[] = [
                        'tipo' => 'padrao_pdf24',
                        'variavel' => 'email',
                        'texto_antes' => self::truncarRelatorio($innerRaw),
                        'origem' => 'pdf24_formato',
                        'atributos_span' => $pdfClasse ?? self::truncarRelatorio($attrs, self::RELATORIO_ATTRS_MAX),
                    ];

                    return '<span'.$attrs.'>{{ $email ?? \'\' }}</span>';
                }
                if (str_contains($inner, '@')) {
                    return $m[0];
                }
                $key = self::inferirChavePorFormatoBr($inner, $allowed);
                if ($key === null) {
                    return $m[0];
                }
                $itens[] = [
                    'tipo' => 'padrao_pdf24',
                    'variavel' => $key,
                    'texto_antes' => self::truncarRelatorio($innerRaw),
                    'origem' => 'pdf24_formato',
                    'atributos_span' => $pdfClasse ?? self::truncarRelatorio($attrs, self::RELATORIO_ATTRS_MAX),
                ];

                return '<span'.$attrs.'>{{ $'.$key." ?? '' }}</span>";
            },
            $html
        );

        return is_string($out) ? $out : $html;
    }

    /**
     * @param  array<string, true>  $allowed
     */
    private static function inferirChavePorFormatoBr(string $inner, array $allowed): ?string
    {
        if (preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $inner) && isset($allowed['cpf'])) {
            return 'cpf';
        }
        if (preg_match('/^\d{5}-\d{3}$/', $inner) && isset($allowed['cep'])) {
            return 'cep';
        }
        if (preg_match('/^\(\d{2}\)\s*(\d{4,5})-(\d{4})$/', $inner, $pm) && isset($allowed['celular'])) {
            if (strlen($pm[1]) === 5 && $pm[1][0] === '9') {
                return 'celular';
            }
        }
        if (preg_match('/^\(\d{2}\)\s*\d{4,5}-\d{4}$/', $inner) && isset($allowed['telefone'])) {
            return 'telefone';
        }
        if (preg_match('/^\d{3}[A-Za-z]\d{10,}$/', $inner) && isset($allowed['cha_numero'])) {
            return 'cha_numero';
        }

        return null;
    }

    /**
     * @param  array<string, true>  $allowed
     * @param  list<array<string, mixed>>  $itens
     */
    private static function aplicarAliasesEmSpansSimples(string $html, array $allowed, array &$itens): string
    {
        $out = preg_replace_callback(
            '/<span(\s[^>]*)>([^<]{1,64})<\/span>/iu',
            static function (array $m) use ($allowed, &$itens): string {
                $attrs = $m[1];
                if (preg_match('/\bdata-nx\s*=/i', $attrs) || preg_match('/\bdata-campo\s*=/i', $attrs)) {
                    return $m[0];
                }
                $inner = trim(html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($inner === '' || str_contains($inner, '{{') || str_contains($inner, '@')) {
                    return $m[0];
                }
                if (self::eApenasPlaceholderVisual($inner)) {
                    return $m[0];
                }
                $norm = self::normalizarLabel($inner);
                $key = self::ALIAS_TO_KEY[$norm] ?? null;
                if ($key === null || ! isset($allowed[$key])) {
                    return $m[0];
                }
                $itens[] = [
                    'tipo' => 'alias',
                    'variavel' => $key,
                    'texto_antes' => self::truncarRelatorio($inner),
                    'origem' => 'alias_texto',
                    'atributos_span' => preg_match('/\b(?:pdf24_\d+|pdf24_valor)\b/i', $attrs)
                        ? (preg_match('/\b(pdf24_(?:\d+|valor))\b/i', $attrs, $cm) ? $cm[1] : self::truncarRelatorio($attrs, self::RELATORIO_ATTRS_MAX))
                        : null,
                ];

                return '<span'.$attrs.'>{{ $'.$key." ?? '' }}</span>";
            },
            $html
        );

        return is_string($out) ? $out : $html;
    }

    /** Underlines, pontos e traços típicos de PDF24 — não mapear para variável. */
    private static function eApenasPlaceholderVisual(string $inner): bool
    {
        return (bool) preg_match('/^[\s\x{00a0}._…\-–—,;:\/\\\\|]+$/u', $inner);
    }

    private static function extrairInteriorSpan(string $full): ?string
    {
        if (preg_match('/<span\s[^>]*>([\s\S]*)<\/span>/iu', $full, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function normalizarLabel(string $text): string
    {
        $t = Str::lower(trim($text));
        $t = str_replace([':', '…', '•'], '', $t);
        $t = preg_replace('/\s+/u', ' ', $t) ?? $t;

        return Str::ascii($t);
    }
}
