<?php

use App\Support\Normam211DocumentoCodigos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consolida tipos de documento repetidos (nome/código equivalente) e reponta pivots / processos.
 *
 * @see database/migrations/2026_04_21_120000_merge_documento_procurador_opcional_codigos.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('documento_tipos')) {
            return;
        }

        foreach ($this->mergeDefinitions() as $def) {
            $canon = $def['canon'];
            $nome = $def['nome'];
            /** @var list<string> $aliases */
            $aliases = $def['aliases'];
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
        }

        $this->dedupeDocumentoProcessoPivot();
        $this->dedupeProcessoDocumentos();
    }

    /**
     * @return list<array{canon: string, nome: string, aliases: list<string>}>
     */
    private function mergeDefinitions(): array
    {
        $docIdentAliases = [];
        for ($i = 1; $i <= 16; $i++) {
            $docIdentAliases[] = sprintf('TIE_%02d_DOC_IDENTIFICACAO', $i);
        }

        $duasFotosEmbAliases = [];
        foreach (range(1, 16) as $i) {
            if (in_array($i, [2, 3, 12, 16], true)) {
                continue;
            }
            $duasFotosEmbAliases[] = sprintf('TIE_%02d_DUAS_FOTOS', $i);
        }

        $residOuDeclAliases = [
            'TIE_01_COMPROV_RESID_OU_DECL',
            'TIE_06_COMPROV_RESID_OU_DECL',
            'TIE_07_COMPROV_RESID_OU_DECL',
            'TIE_08_COMPROV_RESID_OU_DECL',
            'TIE_10_COMPROV_RESID_OU_DECL',
            'TIE_11_COMPROV_RESID_OU_DECL',
            'TIE_13_COMPROV_RESID_ATE_120_OU_DECL',
        ];

        return [
            [
                'canon' => Normam211DocumentoCodigos::CNH_OU_RG,
                'nome' => 'CNH ou RG do interessado.',
                'aliases' => ['CHA_CNH_OU_RG', 'CIR_CNH_VALIDA_OU_RG'],
            ],
            [
                'canon' => 'TIE_DOC_IDENTIDADE_OU_CONTRATO_SOCIAL',
                'nome' => 'Documento de identificação (RG, CNH) e/ou contrato social (PJ).',
                'aliases' => $docIdentAliases,
            ],
            [
                'canon' => 'TIE_FOTOS_MOTO_AQUATICA',
                'nome' => 'Duas fotos da moto aquática.',
                'aliases' => ['TIE_02_DUAS_FOTOS', 'TIE_03_DUAS_FOTOS', 'TIE_12_DUAS_FOTOS', 'TIE_16_DUAS_FOTOS'],
            ],
            [
                'canon' => Normam211DocumentoCodigos::TIE_DUAS_FOTOS_EMBARCACAO,
                'nome' => 'Duas fotos da embarcação.',
                'aliases' => $duasFotosEmbAliases,
            ],
            [
                'canon' => 'TIE_FOTOS_EMBARCACAO_LATERAL_POPA',
                'nome' => 'Duas fotos da embarcação (vista lateral e popa).',
                'aliases' => ['TIE_01_DUAS_FOTOS_EMBARCACAO'],
            ],
            [
                'canon' => 'TIE_NOTA_MOTOR_ACIMA_50HP',
                'nome' => 'Nota fiscal ou documento comprobatório do motor (acima de 50 HP).',
                'aliases' => ['TIE_01_NOTA_MOTOR_ACIMA_50HP'],
            ],
            [
                'canon' => 'TIE_NOTA_FISCAL_MOTO_AQUATICA',
                'nome' => 'Nota fiscal da moto aquática.',
                'aliases' => ['TIE_02_NOTA_FISCAL'],
            ],
            [
                'canon' => 'TIE_DOC_PROPRIEDADE_EMBARCACAO',
                'nome' => 'Documento de propriedade da embarcação.',
                'aliases' => ['TIE_01_DOC_PROPRIEDADE_EMBARCACAO'],
            ],
            [
                'canon' => 'TIE_CATALOGO_MANUAL_DECL_TECNICA',
                'nome' => 'Catálogo, manual ou declaração técnica da embarcação.',
                'aliases' => ['TIE_01_CATALOGO_MANUAL_DECL_TECNICA'],
            ],
            [
                'canon' => Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP,
                'nome' => 'Comprovante de residência ou Declaração (Anexo 2-G da NORMAM-211, Anexo 2-P da NORMAM-201 ou Anexo 2-P da NORMAM-202).',
                'aliases' => $residOuDeclAliases,
            ],
            [
                'canon' => 'TIE_COMPROVANTE_RESID_212_1C',
                'nome' => 'Comprovante de residência (até 120 dias) ou Declaração de Residência (Anexo 1-C da NORMAM-212).',
                'aliases' => ['TIE_02_COMPROV_RESID_ATE_120_1C', 'TIE_12_COMPROV_RESID_ATE_120_1C', 'TIE_16_COMPROV_RESID_ATE_120_1C'],
            ],
            [
                'canon' => 'TIE_COMPROVANTE_RESID_ATUAL_OU_DECL',
                'nome' => 'Comprovante de residência atualizado (até 120 dias) ou Declaração de Residência.',
                'aliases' => ['TIE_03_COMPROV_RESID_ATUAL_ATE_120_1C'],
            ],
            [
                'canon' => 'TIE_BSADE_211_2B_DUAS_VIAS',
                'nome' => 'BSADE (Anexo 2-B da NORMAM-211) ou  BADE (Anexo 2-B da NORMAM-201 ou Anexo 2-B da NORMAM-202) em duas vias',
                'aliases' => ['TIE_06_BSADE_OU_BADE_2VIAS', 'TIE_07_BSADE_OU_BADE_2VIAS', 'TIE_10_BSADE_OU_BADE_2VIAS', 'TIE_11_BSADE_OU_BADE_2VIAS'],
            ],
            [
                'canon' => 'TIE_BADE_2B_201',
                'nome' => 'BADE (Anexo 2-B da NORMAM-201)',
                'aliases' => ['TIE_04_BADE_2B_201', 'TIE_14_BADE_2B_201'],
            ],
            [
                'canon' => 'TIE_BADE_2B_202',
                'nome' => 'BADE (Anexo 2-B da NORMAM-202)',
                'aliases' => ['TIE_05_BADE_2B_202', 'TIE_15_BADE_2B_202'],
            ],
            [
                'canon' => 'TIE_COMPROV_RESID_ATE_120_2P_201',
                'nome' => 'Comprovante de residência (até 120 dias) (Anexo 2-P da NORMAM-201)',
                'aliases' => ['TIE_04_COMPROV_RESID_ATE_120_2P_201', 'TIE_14_COMPROV_RESID_ATE_120_2P_201'],
            ],
            [
                'canon' => 'TIE_COMPROV_RESID_ATE_120_2P_202',
                'nome' => 'Comprovante de residência (até 120 dias) (Anexo 2-P da NORMAM-202)',
                'aliases' => ['TIE_05_COMPROV_RESID_ATE_120_2P_202', 'TIE_15_COMPROV_RESID_ATE_120_2P_202'],
            ],
            [
                'canon' => 'TIE_TIE_TIEM_ORIGINAL',
                'nome' => 'TIE/TIEM original.',
                'aliases' => ['TIE_TIEM_ORIGINAL', 'TIE_TIEM_ORIGINAL_TRANSF', 'TIE_TIEM_ORIGINAL_JURISD'],
            ],
            [
                'canon' => 'TIE_CERTIFICADOS_TECNICOS',
                'nome' => 'Certificados técnicos (se aplicável).',
                'aliases' => ['TIE_CERTIFICADOS_TECNICOS_TM', 'TIE_05_CERT_TECNICOS'],
            ],
            [
                'canon' => 'TIE_CERT_TECNICOS_ARQUEACAO_SEGURANCA',
                'nome' => 'Certificados técnicos (arqueação e segurança).',
                'aliases' => ['TIE_09_CERT_TECNICOS_ARQ_SEG'],
            ],
            [
                'canon' => 'TIE_CERT_SEGURANCA_ARQUEACAO',
                'nome' => 'Certificados de segurança e arqueação (se aplicável).',
                'aliases' => ['TIE_04_CERT_SEG_ARQ'],
            ],
            [
                'canon' => 'TIE_CERT_SEGURANCA_SE_APLICAVEL',
                'nome' => 'Certificado de segurança (se aplicável).',
                'aliases' => ['TIE_14_CERT_SEG_SE_APLICAVEL'],
            ],
            [
                'canon' => 'TIE_BADE_OU_BSADE',
                'nome' => 'Boletim de Atualização de Embarcação — BADE/BSADE (atualizado ou se houver alteração).',
                'aliases' => ['TIE_BADE_OU_BSADE_ATUALIZADO', 'TIE_BADE_OU_BSADE_SE_ALTERACAO'],
            ],
        ];
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
