<?php

namespace App\Services;

use App\Enums\TipoProcessoCategoria;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Support\ChaChecklistDocumentoCodigos;
use App\Support\Normam211DocumentoCodigos;
use App\Support\NormamDocumentacaoCatalog;
use Illuminate\Support\Facades\DB;

/**
 * Tipos de processo de CHA (Carteira de Habilitação de Amador) — NORMAM 211 / 212.
 *
 * Checklists alinhados às exigências usuais de serviço (procuração e doc. do procurador como itens opcionais).
 *
 * @see NormamDocumentacaoCatalog Mapeamento anexos NORMAM-211/212 e códigos de checklist.
 */
final class HabilitacaoChaProcessosTemplateService
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
        $cat = TipoProcessoCategoria::Cha;

        $procuracao = [
            'codigo' => 'CHA_PROCURACAO',
            'nome' => 'Procuração.',
            'obrigatorio' => false,
        ];
        $docProcurador = [
            'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
            'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
            'obrigatorio' => false,
        ];
        $comprovanteResidencia211 = [
            'codigo' => 'COMPROVANTE_RESIDENCIA_CEP',
            'nome' => 'Comprovante de residência (até 120 dias) ou Declaração (Anexo 2G da NORMAM 211).',
        ];
        $comprovanteResidencia212 = [
            'codigo' => 'CHA_COMPROVANTE_RESIDENCIA_212_2C',
            'nome' => 'Comprovante de residência (até 120 dias) ou Declaração de residência (Anexo 1-C da NORMAM-212).',
        ];
        $cnhComValidade = [
            'codigo' => ChaChecklistDocumentoCodigos::CNH_COM_VALIDADE,
            'nome' => 'CNH: cópia na ficha do cliente é usada automaticamente neste processo, ou anexe outra. Com CNH no processo ou na ficha, o atestado médico/psicofísico é dispensado automaticamente. Sem CNH, use o documento de identidade (RG) e envie o atestado na linha seguinte.',
            'obrigatorio' => false,
        ];
        $atestadoMedicoPsicofisico = [
            'codigo' => ChaChecklistDocumentoCodigos::ATESTADO_MEDICO_PSICOFISICO,
            'nome' => 'Atestado médico/psicofísico (emitido há menos de 1 ano). Obrigatório quando não houver CNH anexada (processo ou ficha do cliente).',
        ];
        $cnhOuRgSimples = [
            'codigo' => Normam211DocumentoCodigos::CNH_OU_RG,
            'nome' => 'CNH ou RG do interessado.',
        ];
        $gru = [
            'codigo' => 'GRU_TAXA_MARINHA',
            'nome' => 'Guia de Recolhimento da União (GRU) e comprovante de pagamento.',
        ];
        $req5h = [
            'codigo' => 'CHA_REQ_ANEXO_5H',
            'nome' => 'Requerimento do interessado (Anexo 5-H da NORMAM 211).',
        ];
        $req5hOcorrencia = [
            'codigo' => 'CHA_REQ_ANEXO_5H_OCORRENCIA',
            'nome' => 'Requerimento do interessado (Anexo 5-H da NORMAM 211), com indicação da ocorrência quando aplicável.',
        ];
        $decl5d = [
            'codigo' => 'CHA_DECL_EXTRAVIO_DANO_ANEXO_5D',
            'nome' => 'Declaração de extravio/dano (Anexo 5-D da NORMAM 211).',
        ];
        $atestadoArrais5e = [
            'codigo' => 'CHA_ATTESTO_TREINO_ARRAIS_5E',
            'nome' => 'Atestado de treinamento náutico de Arrais-Amador (Anexo 5-E da NORMAM 211).',
        ];
        $atestadoMotonauta3b = [
            'codigo' => 'CHA_ATTESTO_TREINO_MOTONAUTA_3B_212',
            'nome' => 'Atestado de treinamento náutico de Motonauta (Anexo 3-B da NORMAM 212).',
        ];
        $comunicadoAula = [
            'codigo' => 'CHA_COMUNICADO_AULA',
            'nome' => 'Comunicado de aula.',
        ];
        $chaCarteira = [
            'codigo' => 'CHA_CARTEIRA_EXISTENTE',
            'nome' => 'Carteira de Habilitação de Amador (CHA).',
        ];
        $chaOuDecl5d = [
            'codigo' => 'CHA_OU_DECL_EXTRAVIO_5D',
            'nome' => 'Carteira de Habilitação de Amador (CHA) ou Declaração de extravio/dano (Anexo 5-D da NORMAM 211).',
        ];
        $curriculoEquivalencia = [
            'codigo' => 'CHA_CURRICULO_CURSO_EQUIVALENCIA',
            'nome' => 'Currículo do curso realizado (que atenda às especificações para a concessão por equivalência).',
        ];
        $certificadoExtraMb = [
            'codigo' => 'CHA_CERTIFICADO_CURSO_EXTRA_MB',
            'nome' => 'Certificado de curso para servidores públicos Extra MB (EANC, ETSP ou ECSP).',
        ];
        $req3a212 = [
            'codigo' => 'CHA_REQ_ANEXO_3A_212',
            'nome' => 'Requerimento do interessado (Anexo 3-A da NORMAM 212).',
        ];
        $decl3dMta = [
            'codigo' => Normam211DocumentoCodigos::CHA_DECL_EXTRAVIO_MTA_3D_212,
            'nome' => 'Declaração de extravio CHA-MTA (Anexo 3-D da NORMAM-212), quando aplicável à categoria.',
        ];

        return [
            [
                'slug' => 'cha-extravio-roubo-furto-dano',
                'nome' => 'Extravio, roubo, furto ou dano de cédula de CHA',
                'categoria' => $cat,
                'documentos' => [
                    $req5hOcorrencia,
                    $procuracao,
                    $docProcurador,
                    $comprovanteResidencia211,
                    $cnhOuRgSimples,
                    $decl5d,
                    $decl3dMta,
                    $gru,
                ],
            ],
            [
                'slug' => 'cha-inscricao-arrais-amador',
                'nome' => 'Inscrição e emissão de Arrais-Amador',
                'categoria' => $cat,
                'documentos' => [
                    $req5h,
                    $procuracao,
                    $docProcurador,
                    $comprovanteResidencia211,
                    $cnhComValidade,
                    $atestadoMedicoPsicofisico,
                    $atestadoArrais5e,
                    $atestadoMotonauta3b,
                    $comunicadoAula,
                    $gru,
                ],
            ],
            [
                'slug' => 'cha-inscricao-arrais-amador-mestre-amador',
                'nome' => 'Inscrição e emissão de Mestre-Amador',
                'categoria' => $cat,
                'documentos' => [
                    $req5h,
                    $procuracao,
                    $docProcurador,
                    $comprovanteResidencia211,
                    $chaCarteira,
                    $cnhComValidade,
                    $atestadoMedicoPsicofisico,
                    $gru,
                ],
            ],
            [
                'slug' => 'cha-renovacao',
                'nome' => 'Renovação da CHA',
                'categoria' => $cat,
                'documentos' => [
                    $req5h,
                    $procuracao,
                    $docProcurador,
                    $comprovanteResidencia211,
                    $cnhComValidade,
                    $atestadoMedicoPsicofisico,
                    $chaOuDecl5d,
                    $gru,
                ],
            ],
            [
                'slug' => 'cha-equivalencia-profissional',
                'nome' => 'Concessão de CHA por equivalência profissional',
                'categoria' => $cat,
                'documentos' => [
                    $req5h,
                    $procuracao,
                    $docProcurador,
                    $comprovanteResidencia211,
                    $curriculoEquivalencia,
                    $certificadoExtraMb,
                    $cnhComValidade,
                    $atestadoMedicoPsicofisico,
                    $gru,
                ],
            ],
            [
                'slug' => 'cha-agregacao-motonauta',
                'nome' => 'Agregação de Motonauta',
                'categoria' => $cat,
                'documentos' => [
                    $req3a212,
                    $procuracao,
                    $docProcurador,
                    $comprovanteResidencia212,
                    $atestadoMotonauta3b,
                    $chaCarteira,
                    $cnhComValidade,
                    $atestadoMedicoPsicofisico,
                    $gru,
                ],
            ],
        ];
    }
}
