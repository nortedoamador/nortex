<?php

namespace App\Services;

use App\Enums\TipoProcessoCategoria;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use Illuminate\Support\Facades\DB;

/**
 * Tipos de serviço na Caderneta de Inscrição e Registro (CIR) — NORMAM 101 (referências 1-L, 1-K etc.).
 */
final class CirProcessosTemplateService
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
        $cat = TipoProcessoCategoria::Cir;

        $procuracao = [
            'codigo' => 'CIR_PROCURACAO',
            'nome' => 'Procuração.',
            'obrigatorio' => false,
        ];
        $docProcurador = [
            'codigo' => 'CIR_DOCUMENTO_PROCURADOR',
            'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
            'obrigatorio' => false,
        ];
        $requerimento = [
            'codigo' => 'CIR_REQUERIMENTO_INTERESSADO',
            'nome' => 'Requerimento do interessado.',
        ];
        $cirDoc = [
            'codigo' => 'CIR_CADERNETA_DOCUMENTO',
            'nome' => 'Caderneta de Inscrição e Registro (CIR).',
        ];
        $cirDocEstrangeiro = [
            'codigo' => 'CIR_CADERNETA_COPIA_AUTENTICADA',
            'nome' => 'CIR (cópia autenticada ou simples com original).',
        ];
        $cnhValidaOuRg = [
            'codigo' => 'CIR_CNH_VALIDA_OU_RG',
            'nome' => 'CNH válida ou RG.',
        ];
        $residenciaBr90 = [
            'codigo' => 'CIR_COMPROVANTE_RESIDENCIA_90_1L',
            'nome' => 'Comprovante de residência (até 90 dias) ou declaração (Anexo 1-L da NORMAM 101).',
        ];
        $residenciaEst90 = [
            'codigo' => 'CIR_COMPROVANTE_RESIDENCIA_90_ESTRANGEIRO',
            'nome' => 'Comprovante de residência (até 90 dias) ou declaração.',
        ];
        $roOuDecl1k = [
            'codigo' => 'CIR_RO_OU_DECL_ANEXO_1K',
            'nome' => 'Registro de ocorrência (RO) ou declaração do fato (Anexo 1-K da NORMAM 101).',
        ];
        $foto3x4 = [
            'codigo' => 'CIR_FOTO_3X4_LOCAL',
            'nome' => 'Foto 3x4 (capturada no local de atendimento).',
        ];
        $foto3x4SeAplicavel = [
            'codigo' => 'CIR_FOTO_3X4_SE_APLICAVEL',
            'nome' => 'Foto 3x4.',
            'obrigatorio' => false,
        ];
        $gru = [
            'codigo' => 'GRU_TAXA_MARINHA',
            'nome' => 'Guia de Recolhimento da União (GRU) e comprovante de pagamento.',
        ];
        $atestadoSaude1Ano = [
            'codigo' => 'CIR_ATESTADO_SAUDE_1_ANO',
            'nome' => 'Atestado de saúde (emitido há menos de 1 ano).',
        ];
        $atestadoSaudeSeCir2Anos = [
            'codigo' => 'CIR_ATESTADO_SAUDE_SE_CIR_MAIS_2_ANOS',
            'nome' => 'Atestado de saúde (se CIR emitida há mais de 2 anos).',
            'obrigatorio' => false,
        ];
        $docEstrangeiro = [
            'codigo' => 'CIR_DOC_IDENTIDADE_ESTRANGEIRO',
            'nome' => 'Documento de identidade de estrangeiro ou visto válido (Polícia Federal).',
        ];
        $cpf = [
            'codigo' => 'CIR_CPF',
            'nome' => 'CPF.',
        ];

        return [
            [
                'slug' => 'cir-2via-extravio-brasileiro',
                'nome' => '2ª via CIR — extravio, dano, roubo ou furto (brasileiros)',
                'categoria' => $cat,
                'documentos' => [
                    $requerimento,
                    $procuracao,
                    $docProcurador,
                    $cirDoc,
                    $cnhValidaOuRg,
                    $residenciaBr90,
                    $roOuDecl1k,
                    $foto3x4,
                    $gru,
                ],
            ],
            [
                'slug' => 'cir-revalidacao-termino-espaco-brasileiro',
                'nome' => 'Revalidação por término de espaço (brasileiros)',
                'categoria' => $cat,
                'documentos' => [
                    $requerimento,
                    $procuracao,
                    $docProcurador,
                    $cirDoc,
                    $cnhValidaOuRg,
                    $residenciaBr90,
                    $atestadoSaude1Ano,
                    $gru,
                ],
            ],
            [
                'slug' => 'cir-2via-extravio-estrangeiro',
                'nome' => '2ª via CIR — extravio, dano, roubo ou furto (estrangeiro)',
                'categoria' => $cat,
                'documentos' => [
                    $requerimento,
                    $procuracao,
                    $docProcurador,
                    $cirDocEstrangeiro,
                    $docEstrangeiro,
                    $cpf,
                    $residenciaEst90,
                    $roOuDecl1k,
                    $foto3x4,
                    $atestadoSaudeSeCir2Anos,
                    $gru,
                ],
            ],
            [
                'slug' => 'cir-revalidacao-termino-espaco-estrangeiro',
                'nome' => 'Revalidação por término de espaço (estrangeiro)',
                'categoria' => $cat,
                'documentos' => [
                    $requerimento,
                    $procuracao,
                    $docProcurador,
                    $cirDocEstrangeiro,
                    $docEstrangeiro,
                    $cpf,
                    $residenciaBr90,
                    $atestadoSaude1Ano,
                    $foto3x4SeAplicavel,
                    $gru,
                ],
            ],
        ];
    }
}
