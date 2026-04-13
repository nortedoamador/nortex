<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuditDocumentoTiposCommand extends Command
{
    protected $signature = 'nx:documento-tipos-auditar
                            {--empresa= : Filtrar por empresa_id}
                            {--format=table : Saída: table, json ou markdown}
                            {--output= : Escrever relatório neste ficheiro (UTF-8)}';

    protected $description = 'Auditoria só leitura: duplicados por modelo_slug, por nome normalizado e tipos órfãos (sem pivot nem processo_documentos)';

    public function handle(): int
    {
        if (! Schema::hasTable('documento_tipos')) {
            $this->components->error('Tabela documento_tipos não existe.');

            return self::FAILURE;
        }

        $empresaId = $this->option('empresa');
        $empresaFilter = filled($empresaId) ? (int) $empresaId : null;

        $report = [
            'gerado_em' => Carbon::now()->toIso8601String(),
            'empresa_id' => $empresaFilter,
            'grupo_a_modelo_slug' => $this->collectGrupoA($empresaFilter),
            'grupo_b_nome_normalizado' => $this->collectGrupoB($empresaFilter),
            'orfaos' => $this->collectOrfaos($empresaFilter),
        ];

        $format = strtolower((string) $this->option('format'));
        $body = match ($format) {
            'json' => $this->renderJson($report),
            'markdown', 'md' => $this->renderMarkdown($report),
            default => null,
        };

        if ($body !== null) {
            $text = $body;
        } else {
            if ($format !== 'table') {
                $this->components->warn('Formato desconhecido; a usar table.');
            }
            $text = $this->renderConsoleTables($report);
        }

        $outPath = $this->option('output');
        $wroteFile = false;
        if (filled($outPath)) {
            $dir = dirname((string) $outPath);
            if (! is_dir($dir)) {
                $this->components->error('Diretório inválido para --output: '.$dir);

                return self::FAILURE;
            }
            file_put_contents((string) $outPath, $text);

            $this->components->info('Relatório escrito em: '.$outPath);
            $wroteFile = true;
        }

        $suppressConsoleBody = $wroteFile && in_array($format, ['json', 'markdown', 'md'], true);
        if (! $suppressConsoleBody) {
            $this->line($text);
        } else {
            $this->line($this->renderSummaryLine($report));
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{empresa_id: int, modelo_slug: string, tipos: list<array<string, mixed>}>
     */
    private function collectGrupoA(?int $empresaFilter): array
    {
        $q = DB::table('documento_tipos')
            ->selectRaw('empresa_id, modelo_slug')
            ->whereNotNull('modelo_slug')
            ->where('modelo_slug', '!=', '')
            ->groupBy('empresa_id', 'modelo_slug')
            ->havingRaw('COUNT(DISTINCT codigo) > 1');

        if ($empresaFilter !== null) {
            $q->where('empresa_id', $empresaFilter);
        }

        $groups = [];
        foreach ($q->get() as $row) {
            $tipos = DB::table('documento_tipos')
                ->where('empresa_id', $row->empresa_id)
                ->where('modelo_slug', $row->modelo_slug)
                ->orderBy('codigo')
                ->get(['id', 'codigo', 'nome', 'modelo_slug', 'auto_gerado'])
                ->map(fn ($t) => (array) $t)
                ->all();

            $groups[] = [
                'empresa_id' => (int) $row->empresa_id,
                'modelo_slug' => (string) $row->modelo_slug,
                'tipos' => $tipos,
            ];
        }

        return $groups;
    }

    /**
     * Nome normalizado: LOWER(TRIM(nome)).
     *
     * @return list<array{empresa_id: int, nome_normalizado: string, tipos: list<array<string, mixed>}>
     */
    private function collectGrupoB(?int $empresaFilter): array
    {
        $keysQ = DB::table('documento_tipos')
            ->selectRaw('empresa_id, LOWER(TRIM(nome)) as nome_n')
            ->groupByRaw('empresa_id, LOWER(TRIM(nome))')
            ->havingRaw('COUNT(*) > 1');

        if ($empresaFilter !== null) {
            $keysQ->where('empresa_id', $empresaFilter);
        }

        $groups = [];
        foreach ($keysQ->get() as $key) {
            $tipos = DB::table('documento_tipos')
                ->where('empresa_id', $key->empresa_id)
                ->whereRaw('LOWER(TRIM(nome)) = ?', [$key->nome_n])
                ->orderBy('codigo')
                ->get(['id', 'codigo', 'nome', 'modelo_slug', 'auto_gerado'])
                ->map(fn ($t) => (array) $t)
                ->all();

            $groups[] = [
                'empresa_id' => (int) $key->empresa_id,
                'nome_normalizado' => (string) $key->nome_n,
                'tipos' => $tipos,
            ];
        }

        return $groups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectOrfaos(?int $empresaFilter): array
    {
        $q = DB::table('documento_tipos as dt')
            ->select(['dt.id', 'dt.empresa_id', 'dt.codigo', 'dt.nome', 'dt.modelo_slug', 'dt.auto_gerado'])
            ->whereNotExists(function ($sub): void {
                $sub->selectRaw('1')
                    ->from('documento_processo as dp')
                    ->join('tipo_processos as tp', 'tp.id', '=', 'dp.tipo_processo_id')
                    ->whereColumn('dp.documento_tipo_id', 'dt.id')
                    ->whereColumn('tp.empresa_id', 'dt.empresa_id');
            })
            ->whereNotExists(function ($sub): void {
                $sub->selectRaw('1')
                    ->from('processo_documentos as pd')
                    ->join('processos as p', 'p.id', '=', 'pd.processo_id')
                    ->whereColumn('pd.documento_tipo_id', 'dt.id')
                    ->whereColumn('p.empresa_id', 'dt.empresa_id');
            })
            ->orderBy('dt.empresa_id')
            ->orderBy('dt.codigo');

        if ($empresaFilter !== null) {
            $q->where('dt.empresa_id', $empresaFilter);
        }

        return $q->get()->map(fn ($r) => (array) $r)->all();
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function renderJson(array $report): string
    {
        return json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function renderMarkdown(array $report): string
    {
        $lines = [];
        $lines[] = '# Auditoria `documento_tipos`';
        $lines[] = '';
        $lines[] = '- **Gerado:** '.($report['gerado_em'] ?? '');
        if ($report['empresa_id'] !== null) {
            $lines[] = '- **Filtro empresa_id:** '.(string) $report['empresa_id'];
        } else {
            $lines[] = '- **Âmbito:** todas as empresas';
        }
        $lines[] = '';
        $lines[] = 'Comando só leitura (SELECT). Nenhuma alteração à base de dados.';
        $lines[] = '';

        $lines[] = '## Grupo A — mesmo `modelo_slug`, vários `codigo`';
        $lines[] = '';
        /** @var list<array{empresa_id: int, modelo_slug: string, tipos: list<array<string, mixed>>}> $ga */
        $ga = $report['grupo_a_modelo_slug'];
        if ($ga === []) {
            $lines[] = '_Nenhum grupo._';
        } else {
            foreach ($ga as $g) {
                $lines[] = sprintf('### Empresa %d · `%s`', $g['empresa_id'], $g['modelo_slug']);
                $lines[] = '';
                $lines[] = '| id | codigo | nome | modelo_slug | auto_gerado |';
                $lines[] = '| --- | --- | --- | --- | --- |';
                foreach ($g['tipos'] as $t) {
                    $lines[] = sprintf(
                        '| %s | `%s` | %s | %s | %s |',
                        $t['id'],
                        str_replace('`', '\\`', (string) $t['codigo']),
                        $this->mdCell((string) $t['nome']),
                        $t['modelo_slug'] !== null && $t['modelo_slug'] !== '' ? '`'.str_replace('`', '\\`', (string) $t['modelo_slug']).'`' : '—',
                        isset($t['auto_gerado']) && $t['auto_gerado'] ? 'sim' : 'não',
                    );
                }
                $lines[] = '';
            }
        }

        $lines[] = '## Grupo B — `nome` normalizado (`LOWER(TRIM(nome))`), revisão humana';
        $lines[] = '';
        /** @var list<array{empresa_id: int, nome_normalizado: string, tipos: list<array<string, mixed>>}> $gb */
        $gb = $report['grupo_b_nome_normalizado'];
        if ($gb === []) {
            $lines[] = '_Nenhum grupo._';
        } else {
            foreach ($gb as $g) {
                $preview = mb_strlen($g['nome_normalizado']) > 80
                    ? mb_substr($g['nome_normalizado'], 0, 80).'…'
                    : $g['nome_normalizado'];
                $lines[] = sprintf('### Empresa %d · «%s»', $g['empresa_id'], str_replace(['|', "\n", "\r"], [' ', ' ', ''], $preview));
                $lines[] = '';
                $lines[] = '_Chave normalizada completa (para pesquisa):_ `'.str_replace('`', '\\`', $g['nome_normalizado']).'`';
                $lines[] = '';
                $lines[] = '| id | codigo | nome | modelo_slug | auto_gerado |';
                $lines[] = '| --- | --- | --- | --- | --- |';
                foreach ($g['tipos'] as $t) {
                    $lines[] = sprintf(
                        '| %s | `%s` | %s | %s | %s |',
                        $t['id'],
                        str_replace('`', '\\`', (string) $t['codigo']),
                        $this->mdCell((string) $t['nome']),
                        $t['modelo_slug'] !== null && $t['modelo_slug'] !== '' ? '`'.str_replace('`', '\\`', (string) $t['modelo_slug']).'`' : '—',
                        isset($t['auto_gerado']) && $t['auto_gerado'] ? 'sim' : 'não',
                    );
                }
                $lines[] = '';
            }
        }

        $lines[] = '## Órfãos — sem `documento_processo` (via `tipo_processos.empresa_id`) e sem `processo_documentos`';
        $lines[] = '';
        /** @var list<array<string, mixed>> $or */
        $or = $report['orfaos'];
        if ($or === []) {
            $lines[] = '_Nenhum registo._';
        } else {
            $lines[] = '| id | empresa_id | codigo | nome | modelo_slug | auto_gerado |';
            $lines[] = '| --- | --- | --- | --- | --- | --- |';
            foreach ($or as $t) {
                $lines[] = sprintf(
                    '| %s | %s | `%s` | %s | %s | %s |',
                    $t['id'],
                    $t['empresa_id'],
                    str_replace('`', '\\`', (string) $t['codigo']),
                    $this->mdCell((string) $t['nome']),
                    $t['modelo_slug'] !== null && $t['modelo_slug'] !== '' ? '`'.str_replace('`', '\\`', (string) $t['modelo_slug']).'`' : '—',
                    isset($t['auto_gerado']) && $t['auto_gerado'] ? 'sim' : 'não',
                );
            }
            $lines[] = '';
        }

        return implode("\n", $lines)."\n";
    }

    private function mdCell(string $text): string
    {
        $t = str_replace(["\r\n", "\r", "\n"], ' ', $text);

        return str_replace('|', '\\|', $t);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function renderConsoleTables(array $report): string
    {
        $buf = new BufferedOutput;
        $style = new SymfonyStyle($this->input, $buf);

        $style->title('Auditoria documento_tipos (só leitura)');
        $style->text('Gerado: '.($report['gerado_em'] ?? ''));
        if ($report['empresa_id'] !== null) {
            $style->text('Filtro empresa_id: '.(string) $report['empresa_id']);
        }

        $style->section('Grupo A — mesmo modelo_slug, vários codigo');
        /** @var list<array{empresa_id: int, modelo_slug: string, tipos: list<array<string, mixed>>}> $ga */
        $ga = $report['grupo_a_modelo_slug'];
        if ($ga === []) {
            $style->text('Nenhum grupo.');
        } else {
            foreach ($ga as $g) {
                $style->writeln(sprintf('<info>Empresa %d</info> · %s', $g['empresa_id'], $g['modelo_slug']));
                $style->table(
                    ['id', 'codigo', 'nome', 'modelo_slug', 'auto_gerado'],
                    array_map(fn (array $t) => [
                        $t['id'],
                        $t['codigo'],
                        $this->truncate((string) $t['nome'], 48),
                        $t['modelo_slug'] ?? '',
                        isset($t['auto_gerado']) && $t['auto_gerado'] ? '1' : '0',
                    ], $g['tipos']),
                );
            }
        }

        $style->section('Grupo B — nome normalizado LOWER(TRIM(nome))');
        /** @var list<array{empresa_id: int, nome_normalizado: string, tipos: list<array<string, mixed>>}> $gb */
        $gb = $report['grupo_b_nome_normalizado'];
        if ($gb === []) {
            $style->text('Nenhum grupo.');
        } else {
            foreach ($gb as $g) {
                $style->writeln(sprintf(
                    '<info>Empresa %d</info> · chave: %s',
                    $g['empresa_id'],
                    $this->truncate($g['nome_normalizado'], 72),
                ));
                $style->table(
                    ['id', 'codigo', 'nome', 'modelo_slug', 'auto_gerado'],
                    array_map(fn (array $t) => [
                        $t['id'],
                        $t['codigo'],
                        $this->truncate((string) $t['nome'], 48),
                        $t['modelo_slug'] ?? '',
                        isset($t['auto_gerado']) && $t['auto_gerado'] ? '1' : '0',
                    ], $g['tipos']),
                );
            }
        }

        $style->section('Órfãos — sem pivot (tipo_processos) e sem processo_documentos');
        /** @var list<array<string, mixed>> $or */
        $or = $report['orfaos'];
        if ($or === []) {
            $style->text('Nenhum registo.');
        } else {
            $style->table(
                ['id', 'empresa_id', 'codigo', 'nome', 'modelo_slug', 'auto_gerado'],
                array_map(fn (array $t) => [
                    $t['id'],
                    $t['empresa_id'],
                    $t['codigo'],
                    $this->truncate((string) $t['nome'], 40),
                    $t['modelo_slug'] ?? '',
                    isset($t['auto_gerado']) && $t['auto_gerado'] ? '1' : '0',
                ], $or),
            );
        }

        $style->newLine();
        $style->text([
            'Resumo:',
            '  Grupo A: '.count($ga).' grupo(s)',
            '  Grupo B: '.count($gb).' grupo(s)',
            '  Órfãos: '.count($or).' tipo(s)',
        ]);

        return $buf->fetch();
    }

    private function truncate(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1).'…';
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function renderSummaryLine(array $report): string
    {
        $ga = $report['grupo_a_modelo_slug'];
        $gb = $report['grupo_b_nome_normalizado'];
        $or = $report['orfaos'];

        return sprintf(
            'Resumo: Grupo A %d grupo(s) · Grupo B %d grupo(s) · Órfãos %d tipo(s) (detalhe no ficheiro).',
            count($ga),
            count($gb),
            count($or),
        );
    }
}
