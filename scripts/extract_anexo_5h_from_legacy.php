<?php

/**
 * Gera resources/views/documento-modelos/defaults/anexo-5h.blade.php a partir do PHP legado (ANEXO 5-H).
 *
 * Fontes (primeira existente): resources/documento-modelos/source/5H.php, Downloads/5H.php, source/2H.php (legado).
 */
$candidates = [
    __DIR__.'/../resources/documento-modelos/source/5H.php',
    'c:/Users/Win/Downloads/5H.php',
    __DIR__.'/../resources/documento-modelos/source/2H.php',
    'c:/Users/Win/Downloads/2H.php',
];

$src = null;
foreach ($candidates as $p) {
    if (is_string($p) && $p !== '' && file_exists($p)) {
        $src = $p;
        break;
    }
}

if ($src === null) {
    fwrite(STDERR, "Nenhum arquivo fonte encontrado (5H.php / 2H.php).\n");
    exit(1);
}

$html = @file_get_contents($src);
if ($html === false || $html === '') {
    fwrite(STDERR, "Não foi possível ler: {$src}\n");
    exit(1);
}

// O legado 5-H tem duas páginas: dois <svg> dentro de <div class="page">. Um único preg até o
// primeiro </svg> cortava a segunda folha.
if (! preg_match_all('/<svg\b[\s\S]*?<\/svg>/i', $html, $svgBlocks) || $svgBlocks[0] === []) {
    fwrite(STDERR, "Nenhum <svg> encontrado em {$src}\n");
    exit(1);
}

$pagesHtml = '';
foreach ($svgBlocks[0] as $oneSvg) {
    $pagesHtml .= "<div class=\"page\">\n{$oneSvg}\n</div>\n\n";
}
$svg = rtrim($pagesHtml);

// Ordem: trechos longos / específicos antes dos curtos.
$reps = [
    "\n         \n         <?php \$cont_bairro = \$bairro; \$cont_bairro = strlen(\$bairro); ?>\n    " => "\n    ",
    '         <?php $cont_bairro = $bairro; $cont_bairro = strlen($bairro); ?>'.PHP_EOL => '',
    'id="tspan322" style="font-size:<?php if($cont_bairro > 19) echo"7.5px"; else echo"11px"; ?>"><?php echo $bairro; ?>' => 'id="tspan322" style="font-size: {{ strlen((string) ($cliente->bairro ?? \'\')) > 19 ? \'7.5px\' : \'11px\' }}">{{ $cliente->bairro }}',
    '<?php echo $endereco . ", " . $numero . " "  . $apartamento . ", " . $complemento; ?>' => '{{ ($cliente->endereco ?? \'\') . \', \' . trim(($cliente->numero ?? \'\').\' \'.($cliente->apartamento ?? \'\')) . \', \' . ($cliente->complemento ?? \'\') }}',
    '<?php // date_default_timezone_set(\'America/Sao_Paulo\'); $dia = date(\'d\'); echo $dia;?>' => '{{ $hoje->format(\'d\') }}',
    '<?php // date_default_timezone_set(\'America/Sao_Paulo\'); $mes = date(\'m\'); echo $mes;?>' => '{{ $hoje->format(\'m\') }}',
    '<?php // date_default_timezone_set(\'America/Sao_Paulo\'); $ano = date(\'Y\'); echo $ano;?>' => '{{ $hoje->format(\'Y\') }}',
    '<?php echo date(\'d\', $dt_emissao); ?>' => '{{ $cliente->data_emissao_rg?->format(\'d\') ?? \'\' }}',
    '<?php echo date(\'m\', $dt_emissao); ?>' => '{{ $cliente->data_emissao_rg?->format(\'m\') ?? \'\' }}',
    '<?php echo date(\'Y\', $dt_emissao); ?>' => '{{ $cliente->data_emissao_rg?->format(\'Y\') ?? \'\' }}',
    '<?php echo $tel; ?>' => '{{ $cliente->telefoneFormatado() ?? $cliente->telefone }}',
    '<?php echo $nome_embarcacao; ?>' => '{{ $embarcacao?->nome ?? $embarcacao?->nome_casco ?? \'\' }}',
    '<?php echo $inscricao; ?>' => '{{ $embarcacao?->inscricao ?? \'\' }}',
    '<?php echo date(\'d\'); ?>' => '{{ $hoje->format(\'d\') }}',
    '<?php echo date(\'m\'); ?>' => '{{ $hoje->format(\'m\') }}',
    '<?php echo date(\'Y\'); ?>' => '{{ $hoje->format(\'Y\') }}',
    '<?php echo $orgao; ?>' => '{{ $cliente->orgao_emissor }}',
    '<?php echo $rg; ?>' => '{{ $cliente->rg ?? $cliente->documento_identidade_numero ?? \'\' }}',
    '<?php echo $nacionalidade; ?>' => '{{ $cliente->nacionalidade }}',
    '<?php echo $naturalidade; ?>' => '{{ $cliente->naturalidade }}',
    '<?php echo $telefone; ?>' => '{{ $cliente->telefoneFormatado() ?? $cliente->telefone }}',
    '<?php echo $celular; ?>' => '{{ $cliente->celularFormatado() ?? $cliente->celular }}',
    '<?php echo $email; ?>' => '{{ $cliente->email }}',
    '<?php echo $cpf; ?>' => '{{ $cliente->cpfFormatado() ?? $cliente->cpf }}',
    '<?php echo $nome; ?>' => '{{ $cliente->nome }}',
    '<?php echo $numero; ?>' => '{{ $cliente->numero }}',
    '<?php echo $complemento; ?>' => '{{ $cliente->complemento }}',
    '<?php echo $cidade; ?>' => '{{ $cliente->cidade }}',
    '<?php echo $uf; ?>' => '{{ $cliente->uf }}',
    '<?php echo $cep; ?>' => '{{ $cliente->cepFormatado() ?? $cliente->cep }}',
    '<?php echo $bairro; ?>' => '{{ $cliente->bairro }}',
    '<?php echo $apartamento; ?>' => '{{ $cliente->apartamento }}',
];

foreach ($reps as $from => $to) {
    $svg = str_replace($from, $to, $svg);
}

if (str_contains($svg, '<?php')) {
    fwrite(STDERR, "Aviso: ainda há fragmentos PHP no SVG; revise o legado ou o mapa de substituições.\n");
}

$bladeHeader = <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ANEXO 5-H — Requerimento (NORMAM 211)</title>
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            html, body { width: 210mm; height: 297mm; }
        }
        * { font-family: Calibri, Carlito, sans-serif; }
        body { text-align: center; background-color: #777; margin: 0; }
        .page { margin: 5px 0; }
        .page svg { background-color: #fff; }
    </style>
</head>
<body>

HTML;

$bladeFooter = <<<'HTML'

</body>
</html>

HTML;

$bladePath = __DIR__.'/../resources/views/documento-modelos/defaults/anexo-5h.blade.php';
file_put_contents($bladePath, $bladeHeader.$svg.$bladeFooter);

$nSvg = count($svgBlocks[0]);
echo "OK fonte={$src} {$nSvg} SVG(ns), ".strlen($svg)." bytes (páginas) -> {$bladePath}\n";
