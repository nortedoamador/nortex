<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('documento_tipos')) {
            return;
        }

        $canon = 'GRU_TAXA_MARINHA';
        $aliases = ['TIE_GRU_COMPROVANTE', 'CHA_GRU_PAGAMENTO'];
        $nome = 'Guia de Recolhimento da União (GRU) e comprovante de pagamento.';
        $codigos = array_merge([$canon], $aliases);

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

                if ($rows->isEmpty()) {
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
        DB::table('documento_processo')
            ->where('empresa_id', $empresaId)
            ->where('documento_tipo_id', $oldTipoId)
            ->update(['documento_tipo_id' => $keepTipoId, 'updated_at' => now()]);

        DB::table('documento_processo')
            ->whereNull('empresa_id')
            ->whereIn('tipo_processo_id', function ($q) use ($empresaId) {
                $q->select('id')->from('tipo_processos')->where('empresa_id', $empresaId);
            })
            ->where('documento_tipo_id', $oldTipoId)
            ->update(['documento_tipo_id' => $keepTipoId, 'updated_at' => now()]);
    }

    private function repointProcessoDocumentos(int $empresaId, int $oldTipoId, int $keepTipoId): void
    {
        DB::table('processo_documentos')
            ->whereIn('processo_id', function ($q) use ($empresaId) {
                $q->select('id')->from('processos')->where('empresa_id', $empresaId);
            })
            ->where('documento_tipo_id', $oldTipoId)
            ->update(['documento_tipo_id' => $keepTipoId, 'updated_at' => now()]);
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
            $keep = array_shift($ids);
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
                DB::table('processo_documento_anexos')
                    ->where('processo_documento_id', (int) $r->id)
                    ->update(['processo_documento_id' => $winnerId, 'updated_at' => now()]);

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
        // Sem rollback: códigos e IDs já fundidos na base.
    }
};
