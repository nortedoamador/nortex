<?php

/**
 * Regenera resources/views/documento-modelos/defaults/anexo-2g.blade.php a partir do PHP legado.
 * Coloque uma cópia do original em resources/documento-modelos/source/2G.php ou use o caminho abaixo.
 */
$src = file_exists(__DIR__.'/../resources/documento-modelos/source/2G.php')
    ? __DIR__.'/../resources/documento-modelos/source/2G.php'
    : 'c:/Users/Win/Downloads/2G.php';
$out = __DIR__.'/../storage/app/anexo-2g-svg-fragment.txt';

$html = file_get_contents($src);
if ($html === false) {
    fwrite(STDERR, "Cannot read: $src\n");
    exit(1);
}

if (! preg_match('/<svg\s[\s\S]*<\/svg>/', $html, $m)) {
    fwrite(STDERR, "SVG not found\n");
    exit(1);
}

$svg = $m[0];

$reps = [
    '<?php echo $nome; ?>' => '{{ $cliente->nome }}',
    '<?php echo $cpf; ?>' => '{{ $cliente->cpfFormatado() ?? $cliente->cpf }}',
    '<?php echo $nacionalidade; ?>' => '{{ $cliente->nacionalidade }}',
    '<?php echo $naturalidade; ?>' => '{{ $cliente->naturalidade }}',
    '<?php echo $telefone; ?>' => '{{ $cliente->telefoneFormatado() ?? $cliente->telefone }}',
    '<?php echo $celular; ?>' => '{{ $cliente->celularFormatado() ?? $cliente->celular }}',
    '<?php echo $email; ?>' => '{{ $cliente->email }}',
    '<?php echo $endereco . " " . $numero; ?>' => '{{ trim(($cliente->endereco ?? \'\') . \' \' . ($cliente->numero ?? \'\')) }}',
    '<?php echo $apartamento . " " .$complemento . " - " . $bairro . ", " . $cidade . " - " . $uf . ", CEP: " . $cep; ?>' => '{{ trim(($cliente->apartamento ?? \'\') . \' \' . ($cliente->complemento ?? \'\') . \' - \' . ($cliente->bairro ?? \'\') . \', \' . ($cliente->cidade ?? \'\') . \' - \' . ($cliente->uf ?? \'\') . \', CEP: \' . ($cliente->cepFormatado() ?? $cliente->cep ?? \'\')) }}',
    '<?php // date_default_timezone_set(\'America/Sao_Paulo\'); $dia = date(\'d\'); echo $dia;?>' => '{{ $hoje->format(\'d\') }}',
    '<?php // $mes = date(\'m\'); echo $mes;?>' => '{{ $hoje->format(\'m\') }}',
    '<?php // $ano = date(\'Y\'); echo $ano;?>' => '{{ $hoje->format(\'Y\') }}',
];

foreach ($reps as $from => $to) {
    $svg = str_replace($from, $to, $svg);
}

file_put_contents($out, $svg);

$bladeHeader = <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ANEXO 2-G - DECLARAÇÃO DE RESIDÊNCIA</title>
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            html, body { width: 210mm; height: 297mm; }
        }
        * { font-family: Calibri, Carlito, sans-serif; }
        body { text-align: center; background-color: #777; margin: 0; }
        .page { margin: 0; }
        .page svg { background-color: #fff; }
    </style>
</head>
<body>
<div class="page">

HTML;

$bladeFooter = <<<'HTML'

</div>
</body>
</html>

HTML;

$bladePath = __DIR__.'/../resources/views/documento-modelos/defaults/anexo-2g.blade.php';
file_put_contents($bladePath, $bladeHeader.$svg.$bladeFooter);

echo 'OK '.strlen($svg).' bytes SVG; blade -> '.$bladePath.PHP_EOL;
