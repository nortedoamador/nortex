<?php

namespace App\Services;

use App\Enums\TipoProcessoCategoria;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Support\Normam211DocumentoCodigos;
use App\Support\NormamDocumentacaoCatalog;
use App\Support\EmbarcacaoTipoServicoCatalogo;
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
                    'ordem' => (int) ($tpl['ordem'] ?? 0),
                ],
            );

            $platformAttr = [];
            if ($platformTipo->nome !== $tpl['nome']) {
                $platformAttr['nome'] = $tpl['nome'];
            }
            $catValue = $tpl['categoria'] instanceof TipoProcessoCategoria ? $tpl['categoria']->value : (string) $tpl['categoria'];
            if (($platformTipo->categoria instanceof TipoProcessoCategoria ? $platformTipo->categoria->value : (string) $platformTipo->categoria) !== $catValue) {
                $platformAttr['categoria'] = $catValue;
            }
            if ((int) $platformTipo->ordem !== (int) ($tpl['ordem'] ?? 0)) {
                $platformAttr['ordem'] = (int) ($tpl['ordem'] ?? 0);
            }
            if (! $platformTipo->ativo) {
                $platformAttr['ativo'] = true;
            }
            if ($platformAttr !== []) {
                $platformTipo->update($platformAttr);
            }

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

            $docTipoIds = array_keys($syncIds);

            DB::transaction(function () use ($empresa, $tipo, $platformTipo, $docTipoIds) {
                $rowsToUpdate = DB::table('documento_processo')
                    ->where('tipo_processo_id', $tipo->id)
                    ->count();

                $conflicts = DB::table('documento_processo as dp')
                    ->select(['dp.documento_tipo_id'])
                    ->whereIn('dp.documento_tipo_id', $docTipoIds)
                    ->where('dp.empresa_id', $empresa->id)
                    ->where('dp.platform_tipo_processo_id', $platformTipo->id)
                    ->where('dp.tipo_processo_id', '!=', $tipo->id)
                    ->groupBy('dp.documento_tipo_id')
                    ->pluck('dp.documento_tipo_id')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->all();

                // Resolve colisões: merge em registo existente e remove duplicado antes do update.
                $merged = [];
                foreach ($conflicts as $docTipoId) {
                    $src = DB::table('documento_processo')
                        ->where('tipo_processo_id', $tipo->id)
                        ->where('documento_tipo_id', $docTipoId)
                        ->first();

                    $dst = DB::table('documento_processo')
                        ->where('empresa_id', $empresa->id)
                        ->where('platform_tipo_processo_id', $platformTipo->id)
                        ->where('documento_tipo_id', $docTipoId)
                        ->first();

                    if (! $src || ! $dst) {
                        continue;
                    }

                    DB::table('documento_processo')
                        ->where('id', $dst->id)
                        ->update([
                            'obrigatorio' => (bool) $dst->obrigatorio || (bool) $src->obrigatorio,
                            'ordem' => min((int) $dst->ordem, (int) $src->ordem),
                            'updated_at' => now(),
                        ]);

                    DB::table('documento_processo')
                        ->where('id', $src->id)
                        ->delete();

                    $merged[] = (int) $docTipoId;
                }

                DB::table('documento_processo')
                    ->where('tipo_processo_id', $tipo->id)
                    ->update([
                        'empresa_id' => $empresa->id,
                        'platform_tipo_processo_id' => $platformTipo->id,
                    ]);
            });
        }
    }

    /**
     * @return list<array{slug: string, nome: string, ordem: int, categoria: TipoProcessoCategoria, documentos: list<array{codigo: string, nome: string, obrigatorio?: bool, ordem?: int}>}>
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
            'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
            'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
            'obrigatorio' => false,
        ];
        $docIdPfPj = [
            'codigo' => 'TIE_DOC_IDENTIDADE_OU_CONTRATO_SOCIAL',
            'nome' => 'Documento de identificação (RG, CNH) e/ou contrato social (PJ).',
        ];
        $docId = [
            'codigo' => 'TIE_DOCUMENTO_IDENTIDADE',
            'nome' => 'Documento de identidade.',
        ];
        $resid211 = [
            'codigo' => Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP,
            'nome' => 'Comprovante de residência ou Declaração (Anexo 2-G da NORMAM-211, Anexo 2-P da NORMAM-201 ou Anexo 2-P da NORMAM-202).',
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
            'nome' => 'Boletim de Atualização de Embarcação — BADE/BSADE (atualizado ou se houver alteração).',
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

        // Documento-base (reuso de blocos existentes)
        $docsInscricaoAte12m = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO_ANEXO_2C_211',
                'nome' => 'Requerimento do interessado (Anexo 2-C da NORMAM-211, Anexo 2-E da NORMAM-201 ou Anexo 2-F da NORMAM-202).',
            ],
            $proc,
            $docProc,
            $docIdPfPj,
            $resid211,
            [
                'codigo' => 'TIE_BSADE_211_2B_DUAS_VIAS',
                'nome' => 'BSADE (Anexo 2-B da NORMAM-211) ou  BADE (Anexo 2-B da NORMAM-201 ou Anexo 2-B da NORMAM-202) em duas vias',
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
        ];

        $docsMotoAquaticaInscricao = [
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
        ];

        $docsInscricaoMarAbertoAb100 = [
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
        ];

        $docsInscricaoInteriorAb100 = [
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
        ];

        $docsRenovacao = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado (Anexo 2-C da NORMAM-211, Anexo 2-E da NORMAM-201 ou Anexo 2-F da NORMAM-202)',
            ],
            $docId,
            $resid211,
            array_merge($badeBsade, ['obrigatorio' => false]),
            $tieOriginal,
            $fotosEmb,
            $seguroDpem,
            $gru,
        ];

        $docsAlteracao = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            $docProc,
            $docId,
            $residSomente,
            array_merge($badeBsade, ['obrigatorio' => true]),
            $tieOriginal,
            [
                'codigo' => 'TIE_DOCS_COMPROVAM_ALTERACAO',
                'nome' => 'Documentos que comprovem a alteração.',
            ],
            $fotosEmb,
            $seguroDpem,
            $gru,
        ];

        $docsTransferJurisdicaoEmbarcacao = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            [
                'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
                'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
                'obrigatorio' => true,
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
        ];

        $docsTransferPropriedadeEr = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            [
                'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
                'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
                'obrigatorio' => true,
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
        ];

        $docsTransferPropriedadeMarAberto = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            [
                'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
                'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
                'obrigatorio' => true,
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
        ];

        $docsTransferPropriedadeInterior = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            [
                'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
                'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
                'obrigatorio' => true,
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
        ];

        $docsRegistroOnus = [
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
        ];

        $docsCancelamentoOnusTm = [
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
        ];

        $docsTmRegistroGrandePorte = [
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
                'codigo' => 'TIE_CERTIFICADOS_TECNICOS',
                'nome' => 'Certificados técnicos (se aplicável).',
                'obrigatorio' => false,
            ],
            $seguroDpem,
            $fotosEmb,
            $gru,
        ];

        $docsTmRegistroInterior = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $docIdPfPj,
            $cpfCnpj,
            $certFiscais,
            $bade,
            [
                'codigo' => 'TIE_CERTIFICADOS_TECNICOS',
                'nome' => 'Certificados técnicos (se aplicável).',
                'obrigatorio' => false,
            ],
            $seguroDpem,
            $fotosEmb,
            $gru,
        ];

        $docsTmRegistroOnus = [
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
        ];

        $docsTmTransfJurisdicao = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            [
                'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
                'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
                'obrigatorio' => true,
            ],
            $docIdPfPj,
            $cpfCnpj,
            $certFiscais,
            $prpmOuJust,
            $seguroDpem,
            $gru,
        ];

        $docsTmTransfPropriedadeBase = [
            [
                'codigo' => 'TIE_REQ_INTERESSADO',
                'nome' => 'Requerimento do interessado.',
            ],
            $proc,
            [
                'codigo' => Normam211DocumentoCodigos::DOCUMENTO_PROCURADOR,
                'nome' => 'Documento oficial do procurador (obrigatório se houver procuração).',
                'obrigatorio' => true,
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
        ];

        $docsTmTransfPropriedadeComPrpm = array_merge($docsTmTransfPropriedadeBase, [$prpmOuJust]);

        $nomesPorSlug = collect(EmbarcacaoTipoServicoCatalogo::listaOrdenada())
            ->mapWithKeys(fn (array $r) => [$r['slug'] => $r['nome']])
            ->all();

        /** @var list<string> $slugsOrdem */
        $slugsOrdem = array_map(static fn (array $r) => $r['slug'], EmbarcacaoTipoServicoCatalogo::listaOrdenada());
        $ordemPorSlug = array_flip($slugsOrdem);

        return [
            [
                'slug' => 'tietie-inscricao-ate-12m',
                'nome' => $nomesPorSlug['tietie-inscricao-ate-12m'],
                'ordem' => (int) ($ordemPorSlug['tietie-inscricao-ate-12m'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsInscricaoAte12m,
            ],
            [
                'slug' => 'tie-inscricao-navegacao-interior-ab100',
                'nome' => $nomesPorSlug['tie-inscricao-navegacao-interior-ab100'],
                'ordem' => (int) ($ordemPorSlug['tie-inscricao-navegacao-interior-ab100'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsInscricaoInteriorAb100,
            ],
            [
                'slug' => 'tie-inscricao-nav-mar-aberto-ab100',
                'nome' => $nomesPorSlug['tie-inscricao-nav-mar-aberto-ab100'],
                'ordem' => (int) ($ordemPorSlug['tie-inscricao-nav-mar-aberto-ab100'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsInscricaoMarAbertoAb100,
            ],
            [
                'slug' => 'tie-inscricao-moto-aquatica',
                'nome' => $nomesPorSlug['tie-inscricao-moto-aquatica'],
                'ordem' => (int) ($ordemPorSlug['tie-inscricao-moto-aquatica'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsMotoAquaticaInscricao,
            ],
            [
                'slug' => 'tie-renovacao',
                'nome' => $nomesPorSlug['tie-renovacao'],
                'ordem' => (int) ($ordemPorSlug['tie-renovacao'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsRenovacao,
            ],
            [
                'slug' => 'tie-alteracao-dados-embarcacao-cpdlag',
                'nome' => $nomesPorSlug['tie-alteracao-dados-embarcacao-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-alteracao-dados-embarcacao-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsAlteracao,
            ],
            [
                'slug' => 'tie-transferencia-propriedade-er-cpdlag',
                'nome' => $nomesPorSlug['tie-transferencia-propriedade-er-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-transferencia-propriedade-er-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTransferPropriedadeEr,
            ],
            [
                'slug' => 'tie-transferencia-propriedade-interior-cpdlag',
                'nome' => $nomesPorSlug['tie-transferencia-propriedade-interior-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-transferencia-propriedade-interior-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTransferPropriedadeInterior,
            ],
            [
                'slug' => 'tie-transferencia-propriedade-mar-aberto-cpdlag',
                'nome' => $nomesPorSlug['tie-transferencia-propriedade-mar-aberto-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-transferencia-propriedade-mar-aberto-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTransferPropriedadeMarAberto,
            ],
            [
                'slug' => 'tie-transferencia-propriedade-moto-aquatica-cpdlag',
                'nome' => $nomesPorSlug['tie-transferencia-propriedade-moto-aquatica-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-transferencia-propriedade-moto-aquatica-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsMotoAquaticaInscricao,
            ],
            [
                'slug' => 'tie-transferencia-jurisdicao-embarcacao-cpdlag',
                'nome' => $nomesPorSlug['tie-transferencia-jurisdicao-embarcacao-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-transferencia-jurisdicao-embarcacao-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTransferJurisdicaoEmbarcacao,
            ],
            [
                'slug' => 'tie-transferencia-jurisdicao-moto-aquatica-cpdlag',
                'nome' => $nomesPorSlug['tie-transferencia-jurisdicao-moto-aquatica-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-transferencia-jurisdicao-moto-aquatica-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsMotoAquaticaInscricao,
            ],
            [
                'slug' => 'tie-registro-onus-averbacoes-cpdlag',
                'nome' => $nomesPorSlug['tie-registro-onus-averbacoes-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-registro-onus-averbacoes-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsRegistroOnus,
            ],
            [
                'slug' => 'tie-cancelamento-onus-averbacoes-cpdlag',
                'nome' => $nomesPorSlug['tie-cancelamento-onus-averbacoes-cpdlag'],
                'ordem' => (int) ($ordemPorSlug['tie-cancelamento-onus-averbacoes-cpdlag'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsCancelamentoOnusTm,
            ],
            [
                'slug' => 'dppprpm-registro-er-grande-porte-ab-gt-100',
                'nome' => $nomesPorSlug['dppprpm-registro-er-grande-porte-ab-gt-100'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-registro-er-grande-porte-ab-gt-100'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmRegistroGrandePorte,
            ],
            [
                'slug' => 'dppprpm-registro-navegacao-interior-ab-gt-100',
                'nome' => $nomesPorSlug['dppprpm-registro-navegacao-interior-ab-gt-100'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-registro-navegacao-interior-ab-gt-100'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmRegistroInterior,
            ],
            [
                'slug' => 'dppprpm-transferencia-propriedade-er-tm',
                'nome' => $nomesPorSlug['dppprpm-transferencia-propriedade-er-tm'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-transferencia-propriedade-er-tm'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmTransfPropriedadeBase,
            ],
            [
                'slug' => 'dppprpm-transferencia-propriedade-interior-tm',
                'nome' => $nomesPorSlug['dppprpm-transferencia-propriedade-interior-tm'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-transferencia-propriedade-interior-tm'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmTransfPropriedadeComPrpm,
            ],
            [
                'slug' => 'dppprpm-transferencia-propriedade-mar-aberto-tm',
                'nome' => $nomesPorSlug['dppprpm-transferencia-propriedade-mar-aberto-tm'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-transferencia-propriedade-mar-aberto-tm'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmTransfPropriedadeComPrpm,
            ],
            [
                'slug' => 'dppprpm-transferencia-jurisdicao-embarcacao-tm',
                'nome' => $nomesPorSlug['dppprpm-transferencia-jurisdicao-embarcacao-tm'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-transferencia-jurisdicao-embarcacao-tm'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmTransfJurisdicao,
            ],
            [
                'slug' => 'dppprpm-registro-onus-averbacoes-tm',
                'nome' => $nomesPorSlug['dppprpm-registro-onus-averbacoes-tm'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-registro-onus-averbacoes-tm'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsTmRegistroOnus,
            ],
            [
                'slug' => 'dppprpm-cancelamento-onus-averbacoes-tm',
                'nome' => $nomesPorSlug['dppprpm-cancelamento-onus-averbacoes-tm'],
                'ordem' => (int) ($ordemPorSlug['dppprpm-cancelamento-onus-averbacoes-tm'] ?? 0),
                'categoria' => $cat,
                'documentos' => $docsCancelamentoOnusTm,
            ],
        ];
    }
}
