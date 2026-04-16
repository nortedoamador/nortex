<?php

use App\Enums\TipoProcessoCategoria;
use App\Support\EmbarcacaoTipoServicoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapeamento de slugs antigos (plataforma) => slugs novos (catálogo embarcação).
     *
     * Importante: alguns tipos antigos não têm equivalente exato na lista nova.
     * Nesses casos usamos um fallback “tie-renovacao” para não quebrar histórico.
     *
     * @return array<string, string>
     */
    private static function mapOldToNewSlug(): array
    {
        return [
            // Inscrições
            'tie-inscricao-embarcacao-ate-12m' => 'tietie-inscricao-ate-12m',
            'tie-inscricao-navegacao-interior-ab100' => 'tie-inscricao-navegacao-interior-ab100',
            'tie-inscricao-mar-aberto-ab100' => 'tie-inscricao-nav-mar-aberto-ab100',
            'tie-inscricao-moto-aquatica' => 'tie-inscricao-moto-aquatica',

            // Renovação
            'tie-renovacao-tie' => 'tie-renovacao',
            'tie-renovacao-moto-aquatica' => 'tie-renovacao',

            // Alterações / Transferências / Ônus (CP/DL/AG)
            'tie-alteracao-dados-embarcacao' => 'tie-alteracao-dados-embarcacao-cpdlag',
            'tie-transferencia-propriedade-esporte-recreio' => 'tie-transferencia-propriedade-er-cpdlag',
            'tie-transferencia-propriedade-navegacao-interior' => 'tie-transferencia-propriedade-interior-cpdlag',
            'tie-transferencia-propriedade-mar-aberto' => 'tie-transferencia-propriedade-mar-aberto-cpdlag',
            'tie-transferencia-jurisdicao' => 'tie-transferencia-jurisdicao-embarcacao-cpdlag',
            'tie-registro-onus-averbacoes' => 'tie-registro-onus-averbacoes-cpdlag',

            // Legados do EmbarcacaoProcessosTemplateService
            'inscricao-embarcacao' => 'tietie-inscricao-ate-12m',
            'renovacao-tie-tiem' => 'tie-renovacao',
            'transferencia-proprietario' => 'tie-transferencia-propriedade-er-cpdlag',
            'transferencia-jurisdicao-embarcacao' => 'tie-transferencia-jurisdicao-embarcacao-cpdlag',
            'segunda-via-tie-tiem' => 'tie-renovacao',

            // Tribunal Marítimo (DPP/PRPM)
            'tie-tm-registro-grande-porte-roteiro-i' => 'dppprpm-registro-er-grande-porte-ab-gt-100',
            'tie-tm-registro-grande-porte-roteiro-ii' => 'dppprpm-registro-er-grande-porte-ab-gt-100',
            'tie-tm-registro-navegacao-interior' => 'dppprpm-registro-navegacao-interior-ab-gt-100',
            'tie-tm-registro-interior-ampliado' => 'dppprpm-registro-navegacao-interior-ab-gt-100',
            'tie-tm-registro-mar-aberto' => 'dppprpm-registro-er-grande-porte-ab-gt-100',
            'tie-tm-registro-mar-aberto-ampliado' => 'dppprpm-registro-er-grande-porte-ab-gt-100',

            'tie-tm-transferencia-propriedade-er' => 'dppprpm-transferencia-propriedade-er-tm',
            'tie-tm-transferencia-propriedade-interior' => 'dppprpm-transferencia-propriedade-interior-tm',
            'tie-tm-transferencia-propriedade-mar-aberto' => 'dppprpm-transferencia-propriedade-mar-aberto-tm',
            'tie-tm-transferencia-jurisdicao' => 'dppprpm-transferencia-jurisdicao-embarcacao-tm',
            'tie-tm-registro-onus-averbacoes' => 'dppprpm-registro-onus-averbacoes-tm',
            'tie-cancelamento-onus-tm' => 'dppprpm-cancelamento-onus-averbacoes-tm',

            // Sem equivalente explícito na lista nova -> fallback (não quebrar histórico)
            'tie-cancelamento-embarcacao' => 'tie-renovacao',
            'tie-cancelamento-embarcacao-tm' => 'dppprpm-cancelamento-onus-averbacoes-tm',
        ];
    }

    private static function fallbackNewSlug(): string
    {
        return 'tie-renovacao';
    }

    public function up(): void
    {
        if (! Schema::hasTable('platform_tipo_processos')) {
            return;
        }

        $lista = EmbarcacaoTipoServicoCatalogo::listaOrdenada();

        // 1) Garante os novos tipos na plataforma (categoria embarcação) com ordem obrigatória.
        $ordem = 0;
        foreach ($lista as $row) {
            DB::table('platform_tipo_processos')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'nome' => $row['nome'],
                    'categoria' => TipoProcessoCategoria::Embarcacao->value,
                    'ativo' => true,
                    'ordem' => $ordem++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        $newSlugToId = DB::table('platform_tipo_processos')
            ->where('categoria', TipoProcessoCategoria::Embarcacao->value)
            ->whereIn('slug', array_map(static fn (array $r) => $r['slug'], $lista))
            ->pluck('id', 'slug')
            ->all();

        // 2) Migra processos + regras de checklist para os novos tipos.
        $map = self::mapOldToNewSlug();
        $fallback = self::fallbackNewSlug();
        $driver = (string) DB::getDriverName();

        // SQLite (tests) não suporta UPDATE ... JOIN; fazemos tudo em PHP.
        if ($driver === 'sqlite') {
            $this->migrarEmPhp($map, $fallback, $newSlugToId);

            return;
        }

        // 2a) platform_tipo_processo_id em processos
        if (Schema::hasTable('processos') && Schema::hasColumn('processos', 'platform_tipo_processo_id')) {
            $this->migrarProcessosComJoin($map, $fallback, $newSlugToId);
        }

        // 2b) documento_processo.platform_tipo_processo_id
        if (Schema::hasTable('documento_processo') && Schema::hasColumn('documento_processo', 'platform_tipo_processo_id')) {
            $this->migrarDocumentoProcessoComJoin($map, $fallback, $newSlugToId);
        }

        // 3) Garante tenant `tipo_processos` para os novos slugs e aponta processos.tipo_processo_id
        if (Schema::hasTable('tipo_processos') && Schema::hasTable('processos')) {
            $this->migrarTipoProcessoTenantPorEmpresa($newSlugToId);
        }

        // 4) Desativa tipos antigos de embarcação fora do novo catálogo.
        $this->desativarTiposAntigos($lista);
    }

    private function migrarEmPhp(array $map, string $fallback, array $newSlugToId): void
    {
        if (! Schema::hasTable('processos')) {
            return;
        }

        $platformRows = DB::table('platform_tipo_processos')
            ->where('categoria', TipoProcessoCategoria::Embarcacao->value)
            ->pluck('slug', 'id')
            ->all();

        // processos
        if (Schema::hasColumn('processos', 'platform_tipo_processo_id')) {
            $processos = DB::table('processos')->select(['id', 'platform_tipo_processo_id'])->get();
            foreach ($processos as $p) {
                $oldId = $p->platform_tipo_processo_id;
                if (! $oldId) {
                    continue;
                }
                $oldSlug = $platformRows[$oldId] ?? null;
                if (! $oldSlug) {
                    continue;
                }
                $newSlug = $map[$oldSlug] ?? $fallback;
                $newId = $newSlugToId[$newSlug] ?? null;
                if (! $newId || (int) $newId === (int) $oldId) {
                    continue;
                }
                DB::table('processos')->where('id', $p->id)->update(['platform_tipo_processo_id' => $newId]);
            }
        }

        // documento_processo
        if (Schema::hasTable('documento_processo') && Schema::hasColumn('documento_processo', 'platform_tipo_processo_id')) {
            $rows = DB::table('documento_processo')->select(['id', 'platform_tipo_processo_id'])->get();
            foreach ($rows as $r) {
                $oldId = $r->platform_tipo_processo_id;
                if (! $oldId) {
                    continue;
                }
                $oldSlug = $platformRows[$oldId] ?? null;
                if (! $oldSlug) {
                    continue;
                }
                $newSlug = $map[$oldSlug] ?? $fallback;
                $newId = $newSlugToId[$newSlug] ?? null;
                if (! $newId || (int) $newId === (int) $oldId) {
                    continue;
                }
                DB::table('documento_processo')->where('id', $r->id)->update(['platform_tipo_processo_id' => $newId]);
            }
        }

        if (Schema::hasTable('tipo_processos') && Schema::hasColumn('processos', 'tipo_processo_id')) {
            $this->migrarTipoProcessoTenantPorEmpresa($newSlugToId);
        }
    }

    private function migrarProcessosComJoin(array $map, string $fallback, array $newSlugToId): void
    {
        // Atualiza em lotes por slug antigo, evitando IN gigantes.
        $oldSlugs = DB::table('platform_tipo_processos')
            ->where('categoria', TipoProcessoCategoria::Embarcacao->value)
            ->pluck('slug')
            ->all();

        foreach ($oldSlugs as $oldSlug) {
            $oldSlug = (string) $oldSlug;
            $newSlug = $map[$oldSlug] ?? $fallback;
            $newId = $newSlugToId[$newSlug] ?? null;
            if (! $newId) {
                continue;
            }

            DB::statement(
                "UPDATE processos p
                 JOIN platform_tipo_processos ptp_old ON ptp_old.id = p.platform_tipo_processo_id
                 SET p.platform_tipo_processo_id = ?
                 WHERE ptp_old.slug = ?",
                [(int) $newId, $oldSlug],
            );
        }
    }

    private function migrarDocumentoProcessoComJoin(array $map, string $fallback, array $newSlugToId): void
    {
        $oldSlugs = DB::table('platform_tipo_processos')
            ->where('categoria', TipoProcessoCategoria::Embarcacao->value)
            ->pluck('slug')
            ->all();

        foreach ($oldSlugs as $oldSlug) {
            $oldSlug = (string) $oldSlug;
            $newSlug = $map[$oldSlug] ?? $fallback;
            $newId = $newSlugToId[$newSlug] ?? null;
            if (! $newId) {
                continue;
            }

            $collisionCount = 0;
            $sample = [];
            try {
                $rows = DB::select(
                    "SELECT dp.id, dp.empresa_id, dp.documento_tipo_id, dp.platform_tipo_processo_id AS old_platform_id
                     FROM documento_processo dp
                     JOIN platform_tipo_processos ptp_old ON ptp_old.id = dp.platform_tipo_processo_id
                     WHERE ptp_old.slug = ?
                       AND EXISTS (
                           SELECT 1 FROM documento_processo dp2
                           WHERE dp2.empresa_id = dp.empresa_id
                             AND dp2.platform_tipo_processo_id = ?
                             AND dp2.documento_tipo_id = dp.documento_tipo_id
                             AND dp2.id <> dp.id
                       )
                     LIMIT 25",
                    [$oldSlug, (int) $newId],
                );
                $collisionCount = count($rows);
                $sample = array_map(static fn ($r) => [
                    'id' => (int) $r->id,
                    'empresa_id' => (int) $r->empresa_id,
                    'documento_tipo_id' => (int) $r->documento_tipo_id,
                    'old_platform_id' => (int) $r->old_platform_id,
                ], $rows);
            } catch (\Throwable $e) {
                // ignore
            }

            if ($collisionCount > 0) {
                // Aborta antes do UPDATE para não estourar a constraint; precisamos deduplicar/mesclar.
                throw new RuntimeException("NX migration collision: documento_processo oldSlug={$oldSlug} => newSlug={$newSlug} (newId={$newId}) would violate doc_proc_emp_plat_doc_unique");
            }

            DB::statement(
                "UPDATE documento_processo dp
                 JOIN platform_tipo_processos ptp_old ON ptp_old.id = dp.platform_tipo_processo_id
                 SET dp.platform_tipo_processo_id = ?
                 WHERE ptp_old.slug = ?",
                [(int) $newId, $oldSlug],
            );
        }
    }

    private function migrarTipoProcessoTenantPorEmpresa(array $newSlugToId): void
    {
        // Garante que exista `tipo_processos` para cada empresa+slug novo.
        $empresas = Schema::hasTable('empresas')
            ? DB::table('empresas')->pluck('id')->map(fn ($id) => (int) $id)->all()
            : DB::table('processos')->distinct()->pluck('empresa_id')->map(fn ($id) => (int) $id)->all();

        $novos = EmbarcacaoTipoServicoCatalogo::listaOrdenada();

        foreach ($empresas as $empresaId) {
            foreach ($novos as $row) {
                DB::table('tipo_processos')->updateOrInsert(
                    ['empresa_id' => $empresaId, 'slug' => $row['slug']],
                    [
                        'nome' => $row['nome'],
                        'categoria' => TipoProcessoCategoria::Embarcacao->value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }

        // Atualiza processos.tipo_processo_id com base no novo platform slug.
        // Processos já estão com platform_tipo_processo_id migrado (ou irão).
        if (! Schema::hasColumn('processos', 'tipo_processo_id')) {
            return;
        }

        foreach (array_keys($newSlugToId) as $slug) {
            $slug = (string) $slug;
            DB::statement(
                "UPDATE processos p
                 JOIN platform_tipo_processos ptp ON ptp.id = p.platform_tipo_processo_id
                 JOIN tipo_processos tp ON tp.empresa_id = p.empresa_id AND tp.slug = ptp.slug
                 SET p.tipo_processo_id = tp.id
                 WHERE ptp.slug = ?",
                [$slug],
            );
        }
    }

    /**
     * @param list<array{slug: string, nome: string}> $lista
     */
    private function desativarTiposAntigos(array $lista): void
    {
        $slugsNovos = array_map(static fn (array $r) => $r['slug'], $lista);

        DB::table('platform_tipo_processos')
            ->where('categoria', TipoProcessoCategoria::Embarcacao->value)
            ->whereNotIn('slug', $slugsNovos)
            ->update(['ativo' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Migração de substituição/migração de dados: não tentamos desfazer automaticamente.
    }
};

