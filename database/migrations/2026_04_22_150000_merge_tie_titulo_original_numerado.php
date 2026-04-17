<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consolida TIE_XX_TIE_ORIGINAL (duplicados numerados) em TIE_TITULO_ORIGINAL.
 *
 * @see database/migrations/2026_04_22_140000_merge_checklist_documento_tipos_duplicados_tie_cha_cir.php
 * @see app\Services\TieProcessosTemplateService (código canónico TIE_TITULO_ORIGINAL)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('documento_tipos')) {
            return;
        }

        $canon = 'TIE_TITULO_ORIGINAL';
        $nome = 'Título de Inscrição de Embarcação — TIE original.';

        $aliases = [];
        for ($i = 1; $i <= 16; $i++) {
            $aliases[] = sprintf('TIE_%02d_TIE_ORIGINAL', $i);
        }

        $codigos = array_values(array_unique(array_merge([$canon], $aliases)));

        $empresaIds = DB::table('documento_tipos')
            ->whereIn('codigo', $codigos)
            ->distinct()
            ->pluck('empresa_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($empresaIds as $empresaId) {
            DB::transaction(function () use ($empresaId, $canon, $nome, $codigos): void {
                $rows = DB::table('documento_tipos')
                    ->where('empresa_id', $empresaId)
                    ->whereIn('codigo', $codigos)
                    ->orderBy('id')
                    ->get();

                if ($rows->count() < 2) {
                    $single = $rows->first();
                    if ($single) {
                        DB::table('documento_tipos')->where('id', (int) $single->id)->update([
                            'codigo' => $canon,
                            'nome' => $nome,
                            'updated_at' => now(),
                        ]);
                    }

                    return;
                }

                $keep = $rows->firstWhere('codigo', $canon) ?? $rows->first();
                $keepId = (int) $keep->id;
                $mergeIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->filter(fn (int $id) => $id !== $keepId)->values()->all();

                DB::table('documento_tipos')->where('id', $keepId)->update([
                    'codigo' => $canon,
                    'nome' => $nome,
                    'updated_at' => now(),
                ]);

                foreach ($mergeIds as $oldId) {
                    $this->repointDocumentoProcesso($empresaId, $oldId, $keepId);
                    $this->repointProcessoDocumentos($empresaId, $oldId, $keepId);
                    DB::table('documento_tipos')->where('id', $oldId)->delete();
                }
            });
        }

        $this->dedupeDocumentoProcessoPivot();
        $this->dedupeProcessoDocumentos();
    }

    private function repointDocumentoProcesso(int $empresaId, int $oldTipoId, int $keepTipoId): void
    {
        $srcRows = DB::table('documento_processo')
            ->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                    ->orWhere(function ($q2) use ($empresaId) {
                        $q2->whereNull('empresa_id')
                            ->whereIn('tipo_processo_id', function ($q3) use ($empresaId) {
                                $q3->select('id')->from('tipo_processos')->where('empresa_id', $empresaId);
                            });
                    });
            })
            ->where('documento_tipo_id', $oldTipoId)
            ->get();

        foreach ($srcRows as $src) {
            $dstQuery = DB::table('documento_processo')
                ->where('documento_tipo_id', $keepTipoId)
                ->where('id', '!=', $src->id);

            if ($src->empresa_id !== null) {
                $dstQuery->where('empresa_id', $src->empresa_id);
            } else {
                $dstQuery->whereNull('empresa_id');
            }

            if ($src->platform_tipo_processo_id !== null) {
                $dstQuery->where('platform_tipo_processo_id', $src->platform_tipo_processo_id);
            } else {
                $dstQuery->whereNull('platform_tipo_processo_id');
            }

            $dst = $dstQuery->first();

            if ($dst) {
                DB::table('documento_processo')->where('id', $dst->id)->update([
                    'obrigatorio' => (bool) $dst->obrigatorio || (bool) $src->obrigatorio,
                    'ordem' => min((int) $dst->ordem, (int) $src->ordem),
                    'updated_at' => now(),
                ]);
                DB::table('documento_processo')->where('id', $src->id)->delete();
            } else {
                DB::table('documento_processo')->where('id', $src->id)->update([
                    'documento_tipo_id' => $keepTipoId,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function repointProcessoDocumentos(int $empresaId, int $oldTipoId, int $keepTipoId): void
    {
        $srcRows = DB::table('processo_documentos')
            ->whereIn('processo_id', function ($q) use ($empresaId) {
                $q->select('id')->from('processos')->where('empresa_id', $empresaId);
            })
            ->where('documento_tipo_id', $oldTipoId)
            ->get();

        foreach ($srcRows as $src) {
            $dst = DB::table('processo_documentos')
                ->where('processo_id', $src->processo_id)
                ->where('documento_tipo_id', $keepTipoId)
                ->where('id', '!=', $src->id)
                ->first();

            if ($dst) {
                $winner = collect([$src, $dst])->sort(function ($a, $b) {
                    $ra = $this->processoDocumentoStatusRank((string) $a->status);
                    $rb = $this->processoDocumentoStatusRank((string) $b->status);
                    if ($ra !== $rb) {
                        return $rb <=> $ra;
                    }

                    return (int) $a->id <=> (int) $b->id;
                })->first();
                $loserId = (int) $winner->id === (int) $src->id ? (int) $dst->id : (int) $src->id;
                $winnerId = (int) $winner->id;

                if (DB::getSchemaBuilder()->hasTable('processo_documento_anexos')) {
                    DB::table('processo_documento_anexos')
                        ->where('processo_documento_id', $loserId)
                        ->update(['processo_documento_id' => $winnerId, 'updated_at' => now()]);
                }

                DB::table('processo_documentos')->where('id', $winnerId)->update([
                    'documento_tipo_id' => $keepTipoId,
                    'updated_at' => now(),
                ]);

                DB::table('processo_documentos')->where('id', $loserId)->delete();
            } else {
                DB::table('processo_documentos')->where('id', $src->id)->update([
                    'documento_tipo_id' => $keepTipoId,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function dedupeDocumentoProcessoPivot(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('documento_processo')) {
            return;
        }

        $rows = DB::table('documento_processo')->orderBy('id')->get(['id', 'empresa_id', 'platform_tipo_processo_id', 'tipo_processo_id', 'documento_tipo_id']);
        $buckets = [];
        foreach ($rows as $r) {
            $key = implode('|', [
                (string) ($r->empresa_id ?? 'null'),
                (string) ($r->platform_tipo_processo_id ?? 'null'),
                (string) ($r->tipo_processo_id ?? 'null'),
                (string) $r->documento_tipo_id,
            ]);
            $buckets[$key][] = (int) $r->id;
        }

        foreach ($buckets as $ids) {
            if (count($ids) <= 1) {
                continue;
            }
            sort($ids);
            array_shift($ids);
            DB::table('documento_processo')->whereIn('id', $ids)->delete();
        }
    }

    private function dedupeProcessoDocumentos(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('processo_documentos')) {
            return;
        }

        $groups = DB::table('processo_documentos')
            ->selectRaw('processo_id, documento_tipo_id, COUNT(*) as c')
            ->groupBy('processo_id', 'documento_tipo_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($groups as $g) {
            $pid = (int) $g->processo_id;
            $tid = (int) $g->documento_tipo_id;
            $rows = DB::table('processo_documentos')
                ->where('processo_id', $pid)
                ->where('documento_tipo_id', $tid)
                ->orderBy('id')
                ->get();

            $winner = $rows->sort(function ($a, $b) {
                $ra = $this->processoDocumentoStatusRank((string) $a->status);
                $rb = $this->processoDocumentoStatusRank((string) $b->status);
                if ($ra !== $rb) {
                    return $rb <=> $ra;
                }

                return (int) $a->id <=> (int) $b->id;
            })->first();
            $winnerId = (int) $winner->id;

            foreach ($rows as $r) {
                if ((int) $r->id === $winnerId) {
                    continue;
                }
                if (DB::getSchemaBuilder()->hasTable('processo_documento_anexos')) {
                    DB::table('processo_documento_anexos')
                        ->where('processo_documento_id', (int) $r->id)
                        ->update(['processo_documento_id' => $winnerId, 'updated_at' => now()]);
                }

                DB::table('processo_documentos')->where('id', (int) $r->id)->delete();
            }
        }
    }

    private function processoDocumentoStatusRank(string $status): int
    {
        return match ($status) {
            'enviado' => 4,
            'fisico' => 3,
            'dispensado' => 2,
            'pendente' => 1,
            default => 0,
        };
    }

    public function down(): void
    {
        // Sem rollback seguro após fusão de IDs.
    }
};
