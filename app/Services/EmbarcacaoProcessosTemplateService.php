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
        $cat = TipoProcessoCategoria::Embarcacao;

        $req2c = [
            'codigo' => 'REQ_NORMAM_2C',
            'nome' => 'Requerimento do interessado conforme Anexo 2-C da NORMAM 211.',
        ];
        $bsade = [
            'codigo' => 'TIE_BSADE_211_2B_DUAS_VIAS',
            'nome' => 'Boletim Simplificado de Atualização de Dados de Embarcação — BSADE (Anexo 2-B da NORMAM-211), em duas vias.',
        ];
        $proc = [
            'codigo' => 'PROCURACAO_REP_LEGAL',
            'nome' => 'Representação legal (se houver): procuração.',
            'obrigatorio' => false,
        ];
        $docProp = [
            'codigo' => 'DOC_PROPRIETARIO_PF_PJ',
            'nome' => 'Documentos do proprietário. Pessoa física: RG ou CNH. Pessoa jurídica: contrato social ou estatuto.',
        ];
        $docComprador = [
            'codigo' => 'DOC_COMPRADOR_PF_PJ',
            'nome' => 'Documentos do comprador. Pessoa física: RG ou CNH. Pessoa jurídica: contrato social ou estatuto.',
        ];
        $residencia = [
            'codigo' => 'COMPROVANTE_RESIDENCIA_CEP',
            'nome' => 'Comprovante de residência (até 120 dias) ou Declaração (Anexo 2G).',
        ];
        $fotos = [
            'codigo' => 'FOTOS_POPA_TRAVES',
            'nome' => 'Fotos digitais da embarcação: popa (traseira) e través (lateral completa).',
        ];
        $gru = [
            'codigo' => 'GRU_TAXA_MARINHA',
            'nome' => 'Guia de Recolhimento da União (GRU) e comprovante de pagamento.',
        ];

        return [
            [
                'slug' => 'inscricao-embarcacao',
                'nome' => 'Inscrição de Embarcação',
                'categoria' => $cat,
                'documentos' => [
                    $req2c,
                    $bsade,
                    $proc,
                    $docProp,
                    $residencia,
                    [
                        'codigo' => 'PROVA_PROPRIEDADE_EMB',
                        'nome' => 'Prova de propriedade: nota fiscal da embarcação; escritura pública; ou documento de aquisição (compra e venda).',
                    ],
                    [
                        'codigo' => 'MOTOR_EMB_POTENCIA',
                        'nome' => 'Motor da embarcação: nota fiscal ou documento de propriedade (obrigatório para motores com potência acima de 50 HP).',
                    ],
                    [
                        'codigo' => 'CONSTRUCAO_EMB',
                        'nome' => 'Construção da embarcação: declaração do fabricante ou Termo de Responsabilidade de Construção (embarcação artesanal).',
                    ],
                    $fotos,
                    [
                        'codigo' => 'IMPORTADA_RF',
                        'nome' => 'Embarcação importada: documento de aquisição; regularização junto à Receita Federal (DI/DUIMP ou equivalente).',
                        'obrigatorio' => false,
                    ],
                    $gru,
                ],
            ],
            [
                'slug' => 'renovacao-tie-tiem',
                'nome' => 'Renovação do TIE/TIEM',
                'categoria' => $cat,
                'documentos' => [
                    $req2c,
                    $bsade,
                    $proc,
                    $docProp,
                    [
                        'codigo' => 'TIE_TIEM_ORIGINAL',
                        'nome' => 'Documento da embarcação: Título de Inscrição de Embarcação (TIE ou TIEM) original.',
                    ],
                    $residencia,
                    $fotos,
                    $gru,
                ],
            ],
            [
                'slug' => 'segunda-via-tie-tiem',
                'nome' => '2ª Via do TIE/TIEM',
                'categoria' => $cat,
                'documentos' => [
                    $req2c,
                    $bsade,
                    $proc,
                    $docProp,
                    [
                        'codigo' => 'DECL_EXTRAVIO_2H',
                        'nome' => 'Declaração de extravio — Anexo 2H da NORMAM 211 (obrigatória em caso de perda, roubo ou extravio do TIE/TIEM).',
                        'obrigatorio' => false,
                    ],
                    $residencia,
                    $fotos,
                    $gru,
                ],
            ],
            [
                'slug' => 'transferencia-proprietario',
                'nome' => 'Transferência de Proprietário',
                'categoria' => $cat,
                'documentos' => [
                    $req2c,
                    $bsade,
                    $proc,
                    $docComprador,
                    [
                        'codigo' => 'TIE_TIEM_ORIGINAL_TRANSF',
                        'nome' => 'Documento da embarcação: Título de Inscrição de Embarcação (TIE ou TIEM) original.',
                    ],
                    [
                        'codigo' => 'COMPROVACAO_TRANSFERENCIA_2K',
                        'nome' => 'Comprovação da transferência: Autorização de transferência de propriedade (Anexo 2K da NORMAM 211) ou autorização no próprio TIE (modelo antigo); assinaturas de comprador e vendedor com reconhecimento por autenticidade (cartório ou assinatura digital). Em caso de extravio do TIE: incluir declaração de extravio (Anexo 2H da NORMAM 211).',
                    ],
                    $residencia,
                    $fotos,
                    $gru,
                ],
            ],
            [
                'slug' => 'transferencia-jurisdicao-embarcacao',
                'nome' => 'Transferência de jurisdição de Embarcação',
                'categoria' => $cat,
                'documentos' => [
                    $req2c,
                    $bsade,
                    $proc,
                    $docProp,
                    [
                        'codigo' => 'TIE_TIEM_ORIGINAL_JURISD',
                        'nome' => 'Documento da embarcação: Título de Inscrição de Embarcação (TIE ou TIEM) original.',
                    ],
                    $residencia,
                    $fotos,
                    $gru,
                ],
            ],
        ];
    }
}
