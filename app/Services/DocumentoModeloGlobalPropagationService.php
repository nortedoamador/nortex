<?php

namespace App\Services;

use App\Models\DocumentoModelo;
use App\Models\DocumentoModeloGlobal;
use App\Models\Empresa;
use Illuminate\Support\Collection;

final class DocumentoModeloGlobalPropagationService
{
    public function __construct(
        private EmpresaProcessosDefaultsService $empresaProcessosDefaults,
    ) {}

    /**
     * Replica o global nas empresas indicadas (ou em todas), excepto personalizados ou slug oculto na empresa.
     *
     * @param  list<int>|null  $empresaIds  null ou lista vazia = todas as empresas
     * @return array{updated: int, skipped_customized: int, skipped_oculto: int}
     */
    public function propagarParaEmpresasNaoPersonalizadas(DocumentoModeloGlobal $global, ?array $empresaIds = null): array
    {
        $empresas = $this->resolverEmpresasAlvo($empresaIds);

        $updated = 0;
        $skippedCustomized = 0;
        $skippedOculto = 0;

        foreach ($empresas as $empresa) {
            if ($empresa->documentoModeloLabSlugEstaOculto($global->slug)) {
                $skippedOculto++;

                continue;
            }

            $existente = DocumentoModelo::query()
                ->withoutGlobalScope('empresa')
                ->where('empresa_id', $empresa->id)
                ->where('slug', $global->slug)
                ->first();

            if ($existente !== null && $existente->personalizado) {
                $skippedCustomized++;

                continue;
            }

            $this->empresaProcessosDefaults->garantirModeloGlobalNaEmpresa($empresa, $global);
            $updated++;
        }

        return [
            'updated' => $updated,
            'skipped_customized' => $skippedCustomized,
            'skipped_oculto' => $skippedOculto,
        ];
    }

    /**
     * @param  list<int>|null  $empresaIds
     * @return Collection<int, Empresa>
     */
    private function resolverEmpresasAlvo(?array $empresaIds): Collection
    {
        $query = Empresa::query()->orderBy('nome');

        if ($empresaIds !== null && $empresaIds !== []) {
            $ids = array_values(array_unique(array_map('intval', $empresaIds)));
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }
}
