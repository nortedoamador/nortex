<?php

/**
 * Converte modelos legados (mysqli + echo) em Blade para DocumentoModelo.
 * Uso: php scripts/convert_normam_php_to_blade.php
 */

declare(strict_types=1);

$base = dirname(__DIR__);
$srcDir = 'C:/Users/Win/Pictures';
$outDir = $base.'/resources/views/documento-modelos/defaults';

$map = [
    '1C.php' => 'anexo-1c-normam212.blade.php',
    '2A.php' => 'anexo-2a-normam212.blade.php',
    '2B.php' => 'anexo-2b-bsade.blade.php',
    '2C.php' => 'anexo-2c-normam211.blade.php',
    '2H.php' => 'anexo-2h-normam211.blade.php',
];

$header = "@include('documento-modelos.partials.normam211-212-vars')\n\n";

foreach ($map as $srcName => $outName) {
    $path = $srcDir.'/'.$srcName;
    if (! is_readable($path)) {
        fwrite(STDERR, "Ignorado (não encontrado): {$path}\n");
        continue;
    }
    $s = file_get_contents($path);

    $pos = stripos($s, '<!DOCTYPE');
    if ($pos !== false) {
        $s = substr($s, $pos);
    }

    $s = preg_replace('/<title>BSADE - <\?php echo \$nome; \?><\/title>/', '<title>BSADE — {{ $nome }}</title>', $s);
    $s = preg_replace('/<title>DECLARAÇÃO DE EXTRAVIO - <\?php echo \$nome; \?><\/title>/i', '<title>Declaração de extravio — {{ $nome }}</title>', $s);
    $s = preg_replace('/<title>REQUERIMENTO - <\?php echo \$nome; \?><\/title>/i', '<title>Requerimento — {{ $nome }}</title>', $s);

    $repl = [
        '<?php echo $nome; ?>' => '{{ $nome }}',
        '<?php echo $cpf; ?>' => '{{ $cpf }}',
        '<?php echo $rg; ?>' => '{{ $rg }}',
        '<?php echo $orgao; ?>' => '{{ $orgao }}',
        '<?php echo $endereco; ?>' => '{{ $endereco }}',
        '<?php echo $numero; ?>' => '{{ $numero }}',
        '<?php echo $bairro; ?>' => '{{ $bairro }}',
        '<?php echo $cidade; ?>' => '{{ $cidade }}',
        '<?php echo $uf; ?>' => '{{ $uf }}',
        '<?php echo $cep; ?>' => '{{ $cep }}',
        '<?php echo $complemento; ?>' => '{{ $complemento }}',
        '<?php echo $apartamento; ?>' => '{{ $apartamento }}',
        '<?php echo $telefone; ?>' => '{{ $telefone }}',
        '<?php echo $tel; ?>' => '{{ $tel }}',
        '<?php echo $celular; ?>' => '{{ $celular }}',
        '<?php echo $email; ?>' => '{{ $email }}',
        '<?php echo $nacionalidade; ?>' => '{{ $nacionalidade }}',
        '<?php echo $naturalidade; ?>' => '{{ $naturalidade }}',
        '<?php echo $nome_embarcacao; ?>' => '{{ $nome_embarcacao }}',
        '<?php echo $inscricao; ?>' => '{{ $inscricao }}',
        '<?php echo $comprimento; ?>' => '{{ $comprimento }}',
        '<?php echo $casco; ?>' => '{{ $casco }}',
        '<?php echo $classificacao; ?>' => '{{ $classificacao }}',
        '<?php echo $tipo; ?>' => '{{ $tipo }}',
        '<?php echo $construtor; ?>' => '{{ $construtor }}',
        '<?php echo $ano; ?>' => '{{ $ano }}',
        '<?php echo $tripulantes; ?>' => '{{ $tripulantes }}',
        '<?php echo $passageiros; ?>' => '{{ $passageiros }}',
        '<?php echo $boca; ?>' => '{{ $boca }}',
        '<?php echo $pontal; ?>' => '{{ $pontal }}',
        '<?php echo $calado; ?>' => '{{ $calado }}',
        '<?php echo $contorno; ?>' => '{{ $contorno }}',
        '<?php echo $material_casco; ?>' => '{{ $material_casco }}',
        '<?php echo $arq_bruta; ?>' => '{{ $arq_bruta }}',
        '<?php echo $arq_liquida; ?>' => '{{ $arq_liquida }}',
        '<?php echo $marca_motor; ?>' => '{{ $marca_motor }}',
        '<?php echo $marca_motor2; ?>' => '{{ $marca_motor2 }}',
        '<?php echo $marca_motor3; ?>' => '{{ $marca_motor3 }}',
        '<?php echo $potmax_motor; ?>' => '{{ $potmax_motor }}',
        '<?php echo $potmax_motor2; ?>' => '{{ $potmax_motor2 }}',
        '<?php echo $potmax_motor3; ?>' => '{{ $potmax_motor3 }}',
        '<?php echo $numero_motor; ?>' => '{{ $numero_motor }}',
        '<?php echo $numero_motor2; ?>' => '{{ $numero_motor2 }}',
        '<?php echo $numero_motor3; ?>' => '{{ $numero_motor3 }}',
        '<?php echo $numero_nf; ?>' => '{{ $numero_nf }}',
        '<?php echo $local_nf; ?>' => '{{ $local_nf }}',
        '<?php echo $vendedor_nf; ?>' => '{{ $vendedor_nf }}',
        '<?php echo $documento_vendedor_nf; ?>' => '{{ $documento_vendedor_nf }}',
        '<?php echo $observacao; ?>' => '{{ $observacao }}',
        '<?php echo $novo_nome_embarcacao; ?>' => '{{ $novo_nome_embarcacao }}',
        '<?php echo $novo_nome_embarcacao2; ?>' => '{{ $novo_nome_embarcacao2 }}',
        '<?php echo $novo_nome_embarcacao3; ?>' => '{{ $novo_nome_embarcacao3 }}',
        '<?php echo $numero_casco; ?>' => '{{ $numero_casco }}',
    ];
    foreach ($repl as $a => $b) {
        $s = str_replace($a, $b, $s);
    }

    $s = str_replace(
        '<?php echo $endereco . " " . $numero; ?>',
        '{{ trim($endereco." ".$numero) }}',
        $s
    );
    $s = str_replace(
        '<?php echo $apartamento . " " .$complemento . " - " . $bairro . ", " . $cidade . " - " . $uf . ", CEP: " . $cep; ?>',
        '{{ trim($apartamento." ".$complemento." - ".$bairro.", ".$cidade." - ".$uf.", CEP: ".$cep) }}',
        $s
    );
    $s = str_replace(
        '<?php echo $endereco . ", " . $numero . ", " . $complemento . " "?>',
        '{{ trim($endereco.", ".$numero.", ".$complemento) }}',
        $s
    );

    $s = preg_replace(
        '/<\?php \$complemento1 = substr\(\$complemento, 0, 6\); echo \$complemento1; \?>/',
        '{{ $complemento1 }}',
        $s
    );
    $s = preg_replace(
        '/<\?php \$complemento2 = substr\(\$complemento, 6\); echo \$complemento2; \?>/',
        '{{ $complemento2 }}',
        $s
    );

    $s = preg_replace(
        '/<\?php echo !empty\(\$dt_nf\) \? date\("d\/m\/Y", strtotime\(\$dt_nf\)\) : ""; \?>/',
        '{{ $dt_nf }}',
        $s
    );
    $s = preg_replace(
        '/<\?php echo !empty\(\$dt_emissao\) \? date\("d\/m\/Y", strtotime\(\$dt_emissao\)\) : ""; \?>/',
        '{{ $dt_emissao_fmt }}',
        $s
    );

    $s = preg_replace('/<\?php echo"GOIÂNIA, " \. date\("d\/m\/y"\); \?>/u', 'GOIÂNIA, {{ $hoje->format(\'d/m/y\') }}', $s);

    $s = preg_replace('/<\?php echo date\(\'d\', \$dt_emissao\); \?>/', '{{ $c->data_emissao_rg ? $c->data_emissao_rg->format(\'d\') : \'\' }}', $s);
    $s = preg_replace('/<\?php echo date\(\'m\', \$dt_emissao\); \?>/', '{{ $c->data_emissao_rg ? $c->data_emissao_rg->format(\'m\') : \'\' }}', $s);
    $s = preg_replace('/<\?php echo date\(\'Y\', \$dt_emissao\); \?>/', '{{ $c->data_emissao_rg ? $c->data_emissao_rg->format(\'Y\') : \'\' }}', $s);
    $s = preg_replace('/<\?php echo date\(\'d\'\); \?>/', '{{ $hoje->format(\'d\') }}', $s);
    $s = preg_replace('/<\?php echo date\(\'m\'\); \?>/', '{{ $hoje->format(\'m\') }}', $s);
    $s = preg_replace('/<\?php echo date\(\'Y\'\); \?>/', '{{ $hoje->format(\'Y\') }}', $s);

    $s = preg_replace(
        '/<\?php \/\/ date_default_timezone_set\(\'America\/Sao_Paulo\'\); \$dia = date\(\'d\'\); echo \$dia;\?>/',
        '{{ $hoje->format(\'d\') }}',
        $s
    );
    $s = preg_replace(
        '/<\?php \/\/ \$mes = date\(\'m\'\); echo \$mes;\?>/',
        '{{ $hoje->format(\'m\') }}',
        $s
    );
    $s = preg_replace(
        '/<\?php \/\/ \$ano = date\(\'Y\'\); echo \$ano;\?>/',
        '{{ $hoje->format(\'Y\') }}',
        $s
    );

    $s = preg_replace(
        '/<\?php \$nome_embarcacao_parte1 = substr\(\$nome_embarcacao, 0, 15\); echo \$nome_embarcacao_parte1; \?>/',
        '{{ $nome_embarcacao_parte1 }}',
        $s
    );
    $s = preg_replace(
        '/<\?php \$nome_embarcacao_parte2 = substr\(\$nome_embarcacao, 15\); echo \$nome_embarcacao_parte2; \?>/',
        '{{ $nome_embarcacao_parte2 }}',
        $s
    );

    if (preg_match_all('/<\?php/', $s, $m)) {
        fwrite(STDERR, "{$outName}: ainda há ".count($m[0])." bloco(s) PHP — rever manualmente.\n");
    }

    $s = preg_replace('/<html(\s[^>]*)?>/i', '<html lang="pt-BR"$1>', $s, 1);

    file_put_contents($outDir.'/'.$outName, $header.$s);
    echo "Gerado {$outName}\n";
}
