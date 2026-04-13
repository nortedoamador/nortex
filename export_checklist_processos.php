<?php

/**
 * Exporta tipos de processo, serviços e checklist para CSV (separador ;).
 *
 * Uso (na raiz do projeto):
 *   c:\xampp\php\php.exe export_checklist_processos.php
 *   c:\xampp\php\php.exe export_checklist_processos.php > processos_checklist.csv
 *
 * PowerShell (UTF-8):
 *   c:\xampp\php\php.exe export_checklist_processos.php | Out-File -Encoding utf8 processos_checklist.csv
 *
 * A fonte autoritativa dos nomes e códigos é cada *ProcessosTemplateService (sincronização na BD via EmpresaProcessosDefaultsService).
 * Este CSV não é importado pela aplicação; regenere-o após alterar os templates PHP para manter o ficheiro alinhado.
 */
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$classes = [
    'Embarcacao' => App\Services\EmbarcacaoProcessosTemplateService::class,
    'Tie' => App\Services\TieProcessosTemplateService::class,
    'CHA' => App\Services\HabilitacaoChaProcessosTemplateService::class,
    'CIR' => App\Services\CirProcessosTemplateService::class,
];

$out = fopen('php://stdout', 'w');
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, [
    'Fonte',
    'Categoria',
    'Slug',
    'Nome_Servico',
    'Ordem',
    'Codigo_Documento',
    'Nome_Documento',
    'Obrigatorio',
], ';');

foreach ($classes as $fonte => $class) {
    $ref = new ReflectionClass($class);
    $m = $ref->getMethod('templates');
    $m->setAccessible(true);
    $svc = $ref->newInstanceWithoutConstructor();
    $templates = $m->invoke($svc);

    foreach ($templates as $tpl) {
        $cat = $tpl['categoria'];
        $catVal = $cat instanceof UnitEnum ? $cat->value : (string) $cat;
        $slug = $tpl['slug'];
        $nomeServ = $tpl['nome'];

        foreach ($tpl['documentos'] as $ordem => $doc) {
            $ob = (isset($doc['obrigatorio']) && $doc['obrigatorio'] === false) ? 'Nao' : 'Sim';
            $ordemLinha = isset($doc['ordem']) ? (int) $doc['ordem'] : (int) $ordem;

            fputcsv($out, [
                $fonte,
                $catVal,
                $slug,
                $nomeServ,
                (string) ($ordemLinha + 1),
                $doc['codigo'],
                $doc['nome'],
                $ob,
            ], ';');
        }
    }
}

fclose($out);
