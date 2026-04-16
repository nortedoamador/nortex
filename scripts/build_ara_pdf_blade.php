<?php

/**
 * Gera resources/views/aulas/pdf/ara.blade.php a partir do HTML pdf24 compilado.
 * c:\xampp\php\php.exe scripts/build_ara_pdf_blade.php
 */
$root = dirname(__DIR__);
$src = $root.'/storage/framework/views/a956cd481b06e442332ed357e8581108.blade.php';
$dst = $root.'/resources/views/aulas/pdf/ara.blade.php';
if (! is_readable($src)) {
    fwrite(STDERR, "Fonte não encontrada: $src\n");
    exit(1);
}
$lines = explode("\n", file_get_contents($src));
if (count($lines) < 4 || ! str_contains($lines[0], '@php')) {
    fwrite(STDERR, "Cabeçalho inesperado\n");
    exit(1);
}
array_shift($lines);
array_shift($lines);
array_shift($lines);
$html = implode("\n", $lines);

$ti = 0;
$html = preg_replace_callback(
    '/<span class="pdf24_60 pdf24_08 pdf24_61">TEMPO &nbsp;<\/span>/',
    function () use (&$ti) {
        $i = $ti++;

        return '<span class="pdf24_60 pdf24_08 pdf24_61">{{ $araPdf[\'tempos\']['.$i.'] }} &nbsp;</span>';
    },
    $html
);

$html = preg_replace_callback(
    '/<span class="pdf24_28 pdf24_08 pdf24_59" style="word-spacing:-0\.0023em;">DATA DA AULA &nbsp;<\/span>|<span class="pdf24_70 pdf24_08 pdf24_59" style="word-spacing:-0\.0023em;">DATA DA AULA<\/span>/',
    function (array $m) {
        if (str_contains($m[0], 'pdf24_70')) {
            return '<span class="pdf24_70 pdf24_08 pdf24_59" style="word-spacing:-0.0023em;">{{ $araPdf[\'data_aula\'] }}</span>';
        }

        return '<span class="pdf24_28 pdf24_08 pdf24_59" style="word-spacing:-0.0023em;">{{ $araPdf[\'data_aula\'] }} &nbsp;</span>';
    },
    $html
);

$html = str_replace(
    '<div class="pdf24_01" style="left:14.4542em;top:37.8676em;"><span class="pdf24_18 pdf24_08 pdf24_24" style="word-spacing:-0.0003em;">DATA DA AULA &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:14.4542em;top:37.8676em;"><span class="pdf24_18 pdf24_08 pdf24_24" style="word-spacing:-0.0003em;">{{ $araPdf[\'data_aula\'] }} &nbsp;</span></div>',
    $html
);

$html = str_replace(
    '<div class="pdf24_01" style="left:15.2125em;top:17.0676em;"><span class="pdf24_18 pdf24_08 pdf24_24" style="word-spacing:-0.0022em;">RG DO INSTRUTOR &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:15.2125em;top:17.0676em;"><span class="pdf24_18 pdf24_08 pdf24_24" style="word-spacing:-0.0022em;">{{ $araPdf[\'aluno_rg\'] }} &nbsp;</span></div>',
    $html
);
$html = str_replace(
    '<div class="pdf24_01" style="left:15.525em;top:18.6718em;"><span class="pdf24_18 pdf24_08 pdf24_26" style="word-spacing:-0.0024em;">EMISSÃO DO RG &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:15.525em;top:18.6718em;"><span class="pdf24_18 pdf24_08 pdf24_26" style="word-spacing:-0.0024em;">{{ $araPdf[\'aluno_emissao_rg\'] }} &nbsp;</span></div>',
    $html
);
$html = str_replace(
    '<div class="pdf24_01" style="left:30.6208em;top:18.7051em;"><span class="pdf24_18 pdf24_08 pdf24_19" style="word-spacing:-0.0004em;">CPF DO INSTRUTOR &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:30.6208em;top:18.7051em;"><span class="pdf24_18 pdf24_08 pdf24_19" style="word-spacing:-0.0004em;">{{ $araPdf[\'aluno_cpf\'] }} &nbsp;</span></div>',
    $html
);

$html = str_replace(
    '<div class="pdf24_01" style="left:14.9083em;top:25.7676em;"><span class="pdf24_18 pdf24_08 pdf24_24" style="word-spacing:-0.0022em;">RG DO INSTRUTOR &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:14.9083em;top:25.7676em;"><span class="pdf24_18 pdf24_08 pdf24_24" style="word-spacing:-0.0022em;">{{ $araPdf[\'instrutor_rg\'] }} &nbsp;</span></div>',
    $html
);
$html = str_replace(
    '<div class="pdf24_01" style="left:15.3667em;top:27.0634em;"><span class="pdf24_18 pdf24_08 pdf24_26" style="word-spacing:-0.0024em;">EMISSÃO DO RG &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:15.3667em;top:27.0634em;"><span class="pdf24_18 pdf24_08 pdf24_26" style="word-spacing:-0.0024em;">{{ $araPdf[\'instrutor_emissao_rg\'] }} &nbsp;</span></div>',
    $html
);
$html = str_replace(
    '<div class="pdf24_01" style="left:26.75em;top:27.0259em;"><span class="pdf24_18 pdf24_08 pdf24_19" style="word-spacing:-0.0004em;">CPF DO INSTRUTOR &nbsp;</span></div>',
    '<div class="pdf24_01" style="left:26.75em;top:27.0259em;"><span class="pdf24_18 pdf24_08 pdf24_19" style="word-spacing:-0.0004em;">{{ $araPdf[\'instrutor_cpf\'] }} &nbsp;</span></div>',
    $html
);

$rep = [
    'NOME DO ALUNO &nbsp;' => '{{ $araPdf[\'aluno_nome\'] }} &nbsp;',
    'CPF DO ALUNO &nbsp;' => '{{ $araPdf[\'aluno_cpf\'] }} &nbsp;',
    'NOME DA ESCOLA &nbsp;' => '{{ $araPdf[\'escola_nome\'] }} &nbsp;',
    'NOME DO INSTRUTOR &nbsp;' => '{{ $araPdf[\'instrutor_nome\'] }} &nbsp;',
    'NOME DO DIRETOR &nbsp;' => '{{ $araPdf[\'diretor_nome\'] }} &nbsp;',
    'ORGAO DO RG INSTRUTOR &nbsp;' => '{{ $araPdf[\'instrutor_orgao_rg\'] }} &nbsp;',
    'CHA DO INSTRUTOR &nbsp;' => '{{ $araPdf[\'instrutor_cha_numero\'] }} &nbsp;',
    '6 (SEIS) &nbsp;' => '{{ $araPdf[\'horas_treinamento_label\'] }} &nbsp;',
];
foreach ($rep as $a => $b) {
    $html = str_replace($a, $b, $html);
}

$html = str_replace('ORGAO DO RG ALUNO &nbsp;', '{{ $araPdf[\'aluno_orgao_rg\'] }} &nbsp;', $html);

$blocks = [
    '<span class="pdf24_62 pdf24_08 pdf24_19" style="word-spacing:-0.0034em;">NOME DO</span>' => '<span class="pdf24_62 pdf24_08 pdf24_19" style="word-spacing:-0.0034em;">{{ $araPdf[\'instrutor_nome\'] }}</span>',
    '<span class="pdf24_62 pdf24_08 pdf24_10" style="word-spacing:0.002em;">CATEGORIA DA &nbsp;</span>' => '<span class="pdf24_62 pdf24_08 pdf24_10" style="word-spacing:0.002em;">{{ $araPdf[\'instrutor_cha_categoria\'] }} &nbsp;</span>',
    '<span class="pdf24_62 pdf24_08 pdf24_13" style="word-spacing:0.0025em;">NUMERO DA CHA &nbsp;</span>' => '<span class="pdf24_62 pdf24_08 pdf24_13" style="word-spacing:0.0025em;">{{ $araPdf[\'instrutor_cha_numero\'] }} &nbsp;</span>',
];
foreach ($blocks as $a => $b) {
    $html = str_replace($a, $b, $html);
}

$html = str_replace(
    '<span class="pdf24_47 pdf24_08 pdf24_48" style="word-spacing:0.0106em;">Número da CHA &nbsp;</span>',
    '<span class="pdf24_47 pdf24_08 pdf24_48" style="word-spacing:0.0106em;">{{ $araPdf[\'instrutor_cha_numero\'] }} &nbsp;</span>',
    $html
);
$html = str_replace(
    '<span class="pdf24_47 pdf24_08 pdf24_37" style="word-spacing:0.0006em;">Número da CHA &nbsp;</span>',
    '<span class="pdf24_47 pdf24_08 pdf24_37" style="word-spacing:0.0006em;">{{ $araPdf[\'instrutor_cha_numero\'] }} &nbsp;</span>',
    $html
);

file_put_contents($dst, $html);
echo "Wrote $dst\n";
