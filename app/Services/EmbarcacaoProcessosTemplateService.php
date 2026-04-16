<?php

namespace App\Services;

use App\Enums\TipoProcessoCategoria;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Support\NormamDocumentacaoCatalog;
use Illuminate\Support\Facades\DB;

/**
 * Tipos de processo de embarcação (NORMAM 211 / Marinha) e exigências de documentos para o checklist.
 *
 * @see NormamDocumentacaoCatalog Mapeamento anexos NORMAM-211/212 e códigos de checklist.
 */
final class EmbarcacaoProcessosTemplateService
{
    public function sincronizar(Empresa $empresa): void
    {
        foreach ($this->templates() as $tpl) {
            $platformTipo = PlatformTipoProcesso::query()->firstOrCreate(
                ['slug' => $tpl['slug']],
                [
                    'nome' => $tpl['nome'],
                    'categoria' => $tpl['categoria'] instanceof TipoProcessoCategoria ? $tpl['categoria']->value : (string) $tpl['categoria'],
                    'ativo' => true,
                    'ordem' => 0,
                ],
            );

            $tipo = TipoProcesso::query()->firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'slug' => $tpl['slug'],
                ],
                [
                    'nome' => $tpl['nome'],
                    'categoria' => $tpl['categoria'],
                ],
            );

            $attr = [];
            if ($tipo->nome !== $tpl['nome']) {
                $attr['nome'] = $tpl['nome'];
            }
            if ($tipo->categoria !== $tpl['categoria']) {
                $attr['categoria'] = $tpl['categoria'];
            }
            if ($attr !== []) {
                $tipo->update($attr);
            }

            $syncIds = [];
            foreach ($tpl['documentos'] as $ordem => $doc) {
                $dt = DocumentoTipo::query()->firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'codigo' => $doc['codigo'],
                    ],
                    [
                        'nome' => $doc['nome'],
                    ],
                );

                if ($dt->nome !== $doc['nome']) {
                    $dt->update(['nome' => $doc['nome']]);
                }

                $syncIds[$dt->id] = [
                    'obrigatorio' => $doc['obrigatorio'] ?? true,
                    'ordem' => $doc['ordem'] ?? $ordem,
                ];
            }

            $tipo->documentoRegras()->sync($syncIds);

            DB::table('documento_processo')
                ->where('tipo_processo_id', $tipo->id)
                ->update([
                    'empresa_id' => $empresa->id,
                    'platform_tipo_processo_id' => $platformTipo->id,
                ]);
        }
    }

    /**
     * @return list<array{slug: string, nome: string, categoria: TipoProcessoCategoria, documentos: list<array{codigo: string, nome: string, obrigatorio?: bool, ordem?: int}>}>
     */
    private function templates(): array
    {
        // Mantido apenas por compatibilidade histórica (templates legados).
        // Os tipos atuais de embarcação são sincronizados em TieProcessosTemplateService.
        return [];
    }
}
