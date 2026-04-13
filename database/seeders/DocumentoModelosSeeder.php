<?php

namespace Database\Seeders;

use App\Models\DocumentoModelo;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Support\Normam211DocumentoCodigos;
use Illuminate\Database\Seeder;

class DocumentoModelosSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::query()->get();
        if ($empresas->isEmpty()) {
            return;
        }

        $conteudo2g = file_get_contents(resource_path('views/documento-modelos/defaults/anexo-2g.blade.php'));
        $conteudo5h = file_get_contents(resource_path('views/documento-modelos/defaults/anexo-5h.blade.php'));
        $conteudo5d = file_get_contents(resource_path('views/documento-modelos/defaults/anexo-5d.blade.php'));
        if (! is_string($conteudo2g) || trim($conteudo2g) === '') {
            return;
        }

        foreach ($empresas as $empresa) {
            DocumentoModelo::query()->updateOrCreate(
                ['empresa_id' => $empresa->id, 'slug' => 'anexo-2g'],
                [
                    'titulo' => 'ANEXO 2-G - Declaração de residência',
                    'referencia' => 'NORMAM-211/DPC',
                    'conteudo' => $conteudo2g,
                    'conteudo_upload_bruto' => $conteudo2g,
                    'upload_mapeamento_pendente' => false,
                ],
            );

            if (is_string($conteudo5h) && trim($conteudo5h) !== '') {
                DocumentoModelo::query()->updateOrCreate(
                    ['empresa_id' => $empresa->id, 'slug' => 'anexo-5h'],
                    [
                        'titulo' => 'ANEXO 5-H - Requerimento (NORMAM 211)',
                        'referencia' => 'NORMAM-211/DPC',
                        'conteudo' => $conteudo5h,
                        'conteudo_upload_bruto' => $conteudo5h,
                        'upload_mapeamento_pendente' => false,
                    ],
                );
            }

            if (is_string($conteudo5d) && trim($conteudo5d) !== '') {
                DocumentoModelo::query()->updateOrCreate(
                    ['empresa_id' => $empresa->id, 'slug' => 'anexo-5d'],
                    [
                        'titulo' => 'ANEXO 5-D - Declaração de extravio/dano (CHA, NORMAM 211)',
                        'referencia' => 'NORMAM-211/DPC',
                        'conteudo' => $conteudo5d,
                        'conteudo_upload_bruto' => $conteudo5d,
                        'upload_mapeamento_pendente' => false,
                    ],
                );
            }

            DocumentoTipo::query()
                ->where('empresa_id', $empresa->id)
                ->where('codigo', Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP)
                ->update([
                    'auto_gerado' => false,
                    'modelo_slug' => 'anexo-2g',
                ]);

            DocumentoTipo::query()
                ->where('empresa_id', $empresa->id)
                ->whereIn('codigo', Normam211DocumentoCodigos::codigosDeclaracaoAnexo5h())
                ->update([
                    'auto_gerado' => false,
                    'modelo_slug' => 'anexo-5h',
                ]);

            DocumentoTipo::query()
                ->where('empresa_id', $empresa->id)
                ->whereIn('codigo', Normam211DocumentoCodigos::codigosDeclaracaoAnexo5d())
                ->update([
                    'auto_gerado' => false,
                    'modelo_slug' => 'anexo-5d',
                ]);
        }
    }
}

