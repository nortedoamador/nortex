<?php

namespace App\Services;

use App\Enums\TipoProcessoCategoria;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Support\Normam211DocumentoCodigos;
use App\Support\NormamDocumentacaoCatalog;
use Illuminate\Support\Facades\DB;

/**
 * Checklists TIE (Título de Inscrição de Embarcação). Categoria {@see TipoProcessoCategoria::Embarcacao} — mesmo agrupamento que embarcação.
 *
 * @see NormamDocumentacaoCatalog Mapeamento anexos NORMAM-211/212 e códigos de checklist.
 */
final class TieProcessosTemplateService
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

        $proc = [
            'codigo' => 'TIE_PROCURACAO',
            'nome' => 'Procuração (se aplicável).',
            'obrigatorio' => false,
        ];
        $docProc = [
            'codigo' => 'TIE_DOC_PROCURADOR',
            'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
            'obrigatorio' => false,
        ];
        $docIdPfPj = [
            'codigo' => 'TIE_DOC_IDENTIDADE_OU_CONTRATO_SOCIAL',
            'nome' => 'Documento de identidade ou contrato social (se pessoa jurídica).',
        ];
        $docId = [
            'codigo' => 'TIE_DOCUMENTO_IDENTIDADE',
            'nome' => 'Documento de identidade.',
        ];
        $resid211 = [
            'codigo' => Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP,
            'nome' => 'Comprovante de residência (até 120 dias) ou Declaração de Residência (Anexo 2-G da NORMAM-211).',
        ];
        $resid212 = [
            'codigo' => 'TIE_COMPROVANTE_RESID_212_1C',
            'nome' => 'Comprovante de residência (até 120 dias) ou Declaração de Residência (Anexo 1-C da NORMAM-212).',
        ];
        $residAtualOuDecl = [
            'codigo' => 'TIE_COMPROVANTE_RESID_ATUAL_OU_DECL',
            'nome' => 'Comprovante de residência atualizado (até 120 dias) ou Declaração de Residência.',
        ];
        $residSomente = [
            'codigo' => 'TIE_COMPROVANTE_RESIDENCIA',
            'nome' => 'Comprovante de residência.',
        ];
        $resid90 = [
            'codigo' => 'TIE_COMPROVANTE_RESID_90_OU_DECL',
            'nome' => 'Comprovante de residência (até 90 dias) ou Declaração de Residência.',
        ];
        $gru = [
            'codigo' => 'GRU_TAXA_MARINHA',
            'nome' => 'Guia de Recolhimento da União (GRU) e comprovante de pagamento.',
        ];
        $seguroDpem = [
            'codigo' => 'TIE_SEGURO_DPEM_QUITADO',
            'nome' => 'Seguro obrigatório DPEM quitado.',
        ];
        $seguroDpemExcetoDesmanche = [
            'codigo' => 'TIE_SEGURO_DPEM_QUITADO_EXCETO_DESMANCHE',
            'nome' => 'Seguro obrigatório DPEM quitado (exceto em caso de desmanche).',
        ];
        $fotosEmb = [
            'codigo' => 'TIE_FOTOS_EMBARCACAO_LATERAL_POPA',
            'nome' => 'Duas fotos da embarcação (vista lateral e popa).',
        ];
        $fotosMoto = [
            'codigo' => 'TIE_FOTOS_MOTO_AQUATICA',
            'nome' => 'Duas fotos da moto aquática.',
        ];
        $bade = [
            'codigo' => 'TIE_BADE',
            'nome' => 'Boletim de Atualização de Embarcação — BADE.',
        ];
        $badeBsade = [
            'codigo' => 'TIE_BADE_OU_BSADE',
            'nome' => 'Boletim de Atualização de Embarcação — BADE/BSADE.',
        ];
        $badeBsadeAtualizado = [
            'codigo' => 'TIE_BADE_OU_BSADE_ATUALIZADO',
            'nome' => 'Boletim de Atualização de Embarcação — BADE/BSADE atualizado.',
        ];
        $badeBsadeSeAlteracao = [
            'codigo' => 'TIE_BADE_OU_BSADE_SE_ALTERACAO',
            'nome' => 'Boletim de Atualização de Embarcação — BADE/BSADE (se houver alteração).',
            'obrigatorio' => false,
        ];
        $bdmotoSeAlteracao = [
            'codigo' => 'TIE_BDMOTO_SE_ALTERACAO',
            'nome' => 'Boletim de Dados de Moto Aquática — BDMOTO (se houver alteração).',
            'obrigatorio' => false,
        ];
        $tieOriginal = [
            'codigo' => 'TIE_TITULO_ORIGINAL',
            'nome' => 'Título de Inscrição de Embarcação — TIE original.',
        ];
        $cpfCnpj = [
            'codigo' => 'TIE_CPF_OU_CNPJ',
            'nome' => 'CPF ou CNPJ.',
        ];
        $certFiscais = [
            'codigo' => 'TIE_CERTIDOES_FISCAIS',
            'nome' => 'Certidões fiscais.',
        ];
        $prpmOuJust = [
            'codigo' => 'TIE_PRPM_OU_JUSTIFICATIVA',
            'nome' => 'PRPM original ou justificativa.',
        ];

        return [
            [
                'slug' => 'tie-inscricao-embarcacao-ate-12m',
                'nome' => 'Inscrição de embarcação (até 12 metros)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO_ANEXO_2C_211',
                        'nome' => 'Requerimento do interessado (Anexo 2-C da NORMAM-211).',
                    ],
                    $proc,
                    $docProc,
                    $docIdPfPj,
                    $resid211,
                    [
                        'codigo' => 'TIE_BSADE_211_2B_DUAS_VIAS',
                        'nome' => 'Boletim Simplificado de Atualização de Embarcação — BSADE (Anexo 2-B da NORMAM-211), em duas vias.',
                    ],
                    [
                        'codigo' => 'TIE_DOC_PROPRIEDADE_EMBARCACAO',
                        'nome' => 'Documento de propriedade da embarcação.',
                    ],
                    [
                        'codigo' => 'TIE_NOTA_MOTOR_ACIMA_50HP',
                        'nome' => 'Nota fiscal ou documento comprobatório do motor (acima de 50 HP).',
                    ],
                    [
                        'codigo' => 'TIE_DOC_IMPORTACAO',
                        'nome' => 'Documentação de importação (se aplicável).',
                        'obrigatorio' => false,
                    ],
                    $seguroDpem,
                    [
                        'codigo' => 'TIE_CATALOGO_MANUAL_DECL_TECNICA',
                        'nome' => 'Catálogo, manual ou declaração técnica da embarcação.',
                    ],
                    [
                        'codigo' => 'TIE_TERMO_RESP_CONSTRUCAO_PROPRIA',
                        'nome' => 'Termo de responsabilidade (em caso de construção própria).',
                        'obrigatorio' => false,
                    ],
                    $fotosEmb,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-inscricao-moto-aquatica',
                'nome' => 'Inscrição de moto aquática',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO_ANEXO_2A_212',
                        'nome' => 'Requerimento do interessado (Anexo 2-A da NORMAM-212).',
                    ],
                    [
                        'codigo' => 'TIE_BDMOTO_212_2B',
                        'nome' => 'Boletim de Dados de Moto Aquática — BDMOTO (Anexo 2-B da NORMAM-212).',
                    ],
                    $proc,
                    $docProc,
                    $docId,
                    [
                        'codigo' => 'TIE_NOTA_FISCAL_MOTO_AQUATICA',
                        'nome' => 'Nota fiscal da moto aquática.',
                    ],
                    $resid212,
                    $fotosMoto,
                    [
                        'codigo' => 'TIE_REGULARIZACAO_RF_IMPORTADA',
                        'nome' => 'Regularização junto à Receita Federal (se importada).',
                        'obrigatorio' => false,
                    ],
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-renovacao-moto-aquatica',
                'nome' => 'Renovação de moto aquática',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    $docProc,
                    $docId,
                    $residAtualOuDecl,
                    $bdmotoSeAlteracao,
                    $tieOriginal,
                    $fotosMoto,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-inscricao-mar-aberto-ab100',
                'nome' => 'Inscrição — navegação mar aberto (AB ≤ 100)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    $docProc,
                    $docIdPfPj,
                    $resid211,
                    [
                        'codigo' => 'TIE_PROVA_PROPRIEDADE_EMB',
                        'nome' => 'Prova de propriedade da embarcação.',
                    ],
                    $bade,
                    $fotosEmb,
                    [
                        'codigo' => 'TIE_CERT_SEGURANCA_ARQUEACAO',
                        'nome' => 'Certificados de segurança e arqueação (se aplicável).',
                        'obrigatorio' => false,
                    ],
                    [
                        'codigo' => 'TIE_DOCS_TECNICOS_EXIGIDOS',
                        'nome' => 'Documentos técnicos exigidos.',
                    ],
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-inscricao-navegacao-interior-ab100',
                'nome' => 'Inscrição — navegação interior (AB ≤ 100)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    $docProc,
                    $docIdPfPj,
                    $resid211,
                    [
                        'codigo' => 'TIE_PROVA_PROPRIEDADE_EMB',
                        'nome' => 'Prova de propriedade da embarcação.',
                    ],
                    $bade,
                    $fotosEmb,
                    [
                        'codigo' => 'TIE_CERTIFICADOS_TECNICOS',
                        'nome' => 'Certificados técnicos (se aplicável).',
                        'obrigatorio' => false,
                    ],
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-renovacao-tie',
                'nome' => 'Renovação de TIE',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docId,
                    $resid211,
                    $badeBsadeSeAlteracao,
                    $tieOriginal,
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-alteracao-dados-embarcacao',
                'nome' => 'Alteração de dados da embarcação',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    $docProc,
                    $docId,
                    $residSomente,
                    $badeBsadeAtualizado,
                    $tieOriginal,
                    [
                        'codigo' => 'TIE_DOCS_COMPROVAM_ALTERACAO',
                        'nome' => 'Documentos que comprovem a alteração.',
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-cancelamento-embarcacao',
                'nome' => 'Cancelamento de embarcação',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INFORMANDO_MOTIVO',
                        'nome' => 'Requerimento do interessado informando o motivo.',
                    ],
                    $docId,
                    [
                        'codigo' => 'TIE_DOCS_COMPROBAT_MOTIVO',
                        'nome' => 'Documentos comprobatórios do motivo.',
                    ],
                    $tieOriginal,
                    $seguroDpemExcetoDesmanche,
                ],
            ],
            [
                'slug' => 'tie-registro-onus-averbacoes',
                'nome' => 'Registro de ônus e averbações',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docIdPfPj,
                    $badeBsade,
                    [
                        'codigo' => 'TIE_DOC_COMPROBAT_ONUS_AVERBACAO',
                        'nome' => 'Documento comprobatório do ônus ou averbação.',
                    ],
                    [
                        'codigo' => 'TIE_OU_TIEM',
                        'nome' => 'TIE/TIEM.',
                    ],
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-transferencia-jurisdicao',
                'nome' => 'Transferência de jurisdição',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $resid90,
                    [
                        'codigo' => 'TIE_TIE_TIEM_ORIGINAL',
                        'nome' => 'TIE/TIEM original.',
                    ],
                    $badeBsade,
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-transferencia-propriedade-esporte-recreio',
                'nome' => 'Transferência de propriedade (esporte e recreio)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $residSomente,
                    $badeBsade,
                    [
                        'codigo' => 'TIE_TIE_TIEM_ORIGINAL',
                        'nome' => 'TIE/TIEM original.',
                    ],
                    [
                        'codigo' => 'TIE_AUTORIZ_TRANSFER_FIRMA_RECONHECIDA',
                        'nome' => 'Autorização de transferência com firma reconhecida.',
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-transferencia-propriedade-mar-aberto',
                'nome' => 'Transferência de propriedade (mar aberto)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $residSomente,
                    $badeBsade,
                    [
                        'codigo' => 'TIE_TIE_TIEM_ORIGINAL',
                        'nome' => 'TIE/TIEM original.',
                    ],
                    [
                        'codigo' => 'TIE_AUTORIZ_TRANSFER_FIRMA_RECONHECIDA',
                        'nome' => 'Autorização de transferência com firma reconhecida.',
                    ],
                    [
                        'codigo' => 'TIE_CERT_SEGURANCA_SE_APLICAVEL',
                        'nome' => 'Certificado de segurança (se aplicável).',
                        'obrigatorio' => false,
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-transferencia-propriedade-navegacao-interior',
                'nome' => 'Transferência de propriedade (navegação interior)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $residSomente,
                    $badeBsade,
                    [
                        'codigo' => 'TIE_TIE_TIEM_ORIGINAL',
                        'nome' => 'TIE/TIEM original.',
                    ],
                    [
                        'codigo' => 'TIE_AUTORIZ_TRANSFER_FIRMA_RECONHECIDA',
                        'nome' => 'Autorização de transferência com firma reconhecida.',
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-cancelamento-embarcacao-tm',
                'nome' => 'Cancelamento de embarcação (Tribunal Marítimo)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docId,
                    [
                        'codigo' => 'TIE_DOCS_COMPROBAT_GENERICOS',
                        'nome' => 'Documentos comprobatórios.',
                    ],
                    [
                        'codigo' => 'TIE_FORMULARIO_TRIBUNAL_MARITIMO',
                        'nome' => 'Formulário do Tribunal Marítimo.',
                    ],
                    $prpmOuJust,
                    $seguroDpemExcetoDesmanche,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-cancelamento-onus-tm',
                'nome' => 'Cancelamento de ônus (Tribunal Marítimo)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docId,
                    [
                        'codigo' => 'TIE_DOC_QUITACAO_ONUS',
                        'nome' => 'Documento de quitação do ônus.',
                    ],
                    $prpmOuJust,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-grande-porte-roteiro-i',
                'nome' => 'Registro de embarcação (grande porte — TM) — roteiro I',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    [
                        'codigo' => 'TIE_LICENCA_CONSTRUCAO',
                        'nome' => 'Licença de construção.',
                    ],
                    $bade,
                    [
                        'codigo' => 'TIE_CERTIFICADOS_TECNICOS_TM',
                        'nome' => 'Certificados técnicos.',
                    ],
                    $seguroDpem,
                    $fotosEmb,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-navegacao-interior',
                'nome' => 'Registro (navegação interior — TM)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    $bade,
                    [
                        'codigo' => 'TIE_CERTIFICADOS_TECNICOS_TM',
                        'nome' => 'Certificados técnicos.',
                    ],
                    $seguroDpem,
                    $fotosEmb,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-mar-aberto',
                'nome' => 'Registro (mar aberto — TM)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    $bade,
                    [
                        'codigo' => 'TIE_CERTIFICADOS_TECNICOS_TM',
                        'nome' => 'Certificados técnicos.',
                    ],
                    $seguroDpem,
                    $fotosEmb,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-onus-averbacoes',
                'nome' => 'Registro de ônus e averbações (Tribunal Marítimo)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    $docProc,
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    [
                        'codigo' => 'TIE_DOC_COMPROBAT_ONUS_AVERBACAO',
                        'nome' => 'Documento comprobatório do ônus ou averbação.',
                    ],
                    $prpmOuJust,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-grande-porte-roteiro-ii',
                'nome' => 'Registro de embarcação (grande porte — TM) — roteiro II',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    [
                        'codigo' => 'TIE_LICENCA_CONSTRUCAO_OU_REGULARIZACAO',
                        'nome' => 'Licença de construção ou regularização.',
                    ],
                    $bade,
                    [
                        'codigo' => 'TIE_CERT_TECNICOS_ARQUEACAO_SEGURANCA',
                        'nome' => 'Certificados técnicos (arqueação e segurança).',
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-interior-ampliado',
                'nome' => 'Registro — navegação interior (TM) — documentação ampliada',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    $bade,
                    [
                        'codigo' => 'TIE_CERTIFICADOS_TECNICOS_TM',
                        'nome' => 'Certificados técnicos.',
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-registro-mar-aberto-ampliado',
                'nome' => 'Registro — mar aberto (TM) — documentação ampliada',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    $bade,
                    [
                        'codigo' => 'TIE_CERTIFICADOS_TECNICOS_TM',
                        'nome' => 'Certificados técnicos.',
                    ],
                    $fotosEmb,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-transferencia-jurisdicao',
                'nome' => 'Transferência de jurisdição (Tribunal Marítimo)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    $prpmOuJust,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-transferencia-propriedade-er',
                'nome' => 'Transferência de propriedade (esporte e recreio — TM)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    [
                        'codigo' => 'TIE_DOC_TRANSFERENCIA_FIRMA_RECONHECIDA',
                        'nome' => 'Documento de transferência com firma reconhecida.',
                    ],
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-transferencia-propriedade-mar-aberto',
                'nome' => 'Transferência de propriedade (mar aberto — TM)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    [
                        'codigo' => 'TIE_DOC_TRANSFERENCIA_FIRMA_RECONHECIDA',
                        'nome' => 'Documento de transferência com firma reconhecida.',
                    ],
                    $prpmOuJust,
                    $seguroDpem,
                    $gru,
                ],
            ],
            [
                'slug' => 'tie-tm-transferencia-propriedade-interior',
                'nome' => 'Transferência de propriedade (navegação interior — TM)',
                'categoria' => $cat,
                'documentos' => [
                    [
                        'codigo' => 'TIE_REQ_INTERESSADO',
                        'nome' => 'Requerimento do interessado.',
                    ],
                    $proc,
                    [
                        'codigo' => 'TIE_DOC_PROCURADOR_OBRIG',
                        'nome' => 'Documento oficial do procurador.',
                    ],
                    $docIdPfPj,
                    $cpfCnpj,
                    $certFiscais,
                    [
                        'codigo' => 'TIE_DOC_TRANSFERENCIA_FIRMA_RECONHECIDA',
                        'nome' => 'Documento de transferência com firma reconhecida.',
                    ],
                    $prpmOuJust,
                    $seguroDpem,
                    $gru,
                ],
            ],
        ];
    }
}
