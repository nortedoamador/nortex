<?php

namespace App\Support;

/**
 * Visão consolidada da documentação da Autoridade Marítima (NORMAM-211 e NORMAM-212)
 * para o domínio do sistema (checklists, modelos PDF e contexto regulatório).
 *
 * NORMAM-212: normas para motos aquáticas e motonautas.
 * NORMAM-211: normas para atividades de esporte e recreio.
 */
final class NormamDocumentacaoCatalog
{
    /** Chave estável (evita conversão numérica de chaves em arrays PHP). */
    public const NORMAM_ESPORTE_RECREIO = 'normam_211';

    public const NORMAM_MOTO_AQUATICA = 'normam_212';

    /**
     * Estrutura por norma: grupos temáticos e anexos (com códigos de checklist e slugs de modelo quando existirem no sistema).
     *
     * @return array<string, array{titulo: string, grupos: list<array{id: string, titulo: string, itens: list<array{anexo: string, nome: string, descricao: string, checklist_codigos?: list<string>, modelo_slug?: string}>}>}>
     */
    public static function estrutura(): array
    {
        return [
            self::NORMAM_MOTO_AQUATICA => [
                'titulo' => 'NORMAM-212 — Normas da Autoridade Marítima para motos aquáticas e motonautas',
                'grupos' => [
                    [
                        'id' => 'cha_mta',
                        'titulo' => 'Documentação para Motonáutica (CHA-MTA)',
                        'itens' => [
                            [
                                'anexo' => '3-A',
                                'nome' => 'Requerimento CHA-MTA',
                                'descricao' => 'Formulário padrão para solicitar a emissão, a renovação ou a segunda via da habilitação na categoria Motonauta (MTA).',
                                'checklist_codigos' => [Normam211DocumentoCodigos::CHA_REQ_ANEXO_3A_212],
                                'modelo_slug' => 'anexo-3a-cha-mta-normam212',
                            ],
                            [
                                'anexo' => '3-D',
                                'nome' => 'Declaração de extravio CHA-MTA',
                                'descricao' => 'Utilizado em casos de perda, roubo ou furto da carteira original (categoria MTA, NORMAM-212).',
                                'checklist_codigos' => [Normam211DocumentoCodigos::CHA_DECL_EXTRAVIO_MTA_3D_212],
                                'modelo_slug' => 'anexo-3d-extravio-cha-mta-normam212',
                            ],
                        ],
                    ],
                    [
                        'id' => 'tie_bdmoto',
                        'titulo' => 'Documentação para a embarcação (TIE / moto aquática)',
                        'itens' => [
                            [
                                'anexo' => '2-A',
                                'nome' => 'Requerimento de moto aquática',
                                'descricao' => 'Requerimento geral para serviços relacionados à embarcação do tipo moto aquática.',
                                'checklist_codigos' => ['TIE_REQ_INTERESSADO_ANEXO_2A_212'],
                                'modelo_slug' => 'anexo-2a-normam212',
                            ],
                            [
                                'anexo' => '2-B',
                                'nome' => 'BDMOTO — Boletim de dados de moto aquática',
                                'descricao' => 'Documento técnico com as características da embarcação (chassi/HIN, motor, cor, etc.).',
                                'checklist_codigos' => ['TIE_BDMOTO_212_2B', 'TIE_BDMOTO_SE_ALTERACAO'],
                                'modelo_slug' => 'anexo-2b-bdmoto-normam212',
                            ],
                            [
                                'anexo' => '2-C',
                                'nome' => 'Extravio ou dano do TIE',
                                'descricao' => 'Formulário para solicitar nova via do documento da embarcação por perda ou dano físico.',
                                'modelo_slug' => 'anexo-2c-normam212',
                            ],
                            [
                                'anexo' => '2-D',
                                'nome' => 'Autorização para transferência de propriedade',
                                'descricao' => 'Essencial para compra e venda, com reconhecimento de firma em cartório ou assinatura eletrônica ICP-Brasil.',
                            ],
                        ],
                    ],
                    [
                        'id' => 'geral_212',
                        'titulo' => 'Documentos gerais / comprobatórios (NORMAM-212)',
                        'itens' => [
                            [
                                'anexo' => '1-C',
                                'nome' => 'Declaração de residência',
                                'descricao' => 'Utilizada quando o interessado não possui comprovante de endereço em nome próprio.',
                                'checklist_codigos' => ['TIE_COMPROVANTE_RESID_212_1C', 'CHA_COMPROVANTE_RESIDENCIA_212_2C'],
                                'modelo_slug' => 'anexo-1c-normam212',
                            ],
                        ],
                    ],
                ],
            ],
            self::NORMAM_ESPORTE_RECREIO => [
                'titulo' => 'NORMAM-211 — Normas da Autoridade Marítima para atividades de esporte e recreio',
                'grupos' => [
                    [
                        'id' => 'tie_tiem_211',
                        'titulo' => 'Documentação para embarcações (TIE / TIEM)',
                        'itens' => [
                            [
                                'anexo' => '2-B',
                                'nome' => 'BSADE — Boletim simplificado de atualização de embarcações',
                                'descricao' => 'Atualização de dados da embarcação; no sistema, em duas vias quando exigido pelo serviço.',
                                'checklist_codigos' => ['TIE_BSADE_211_2B_DUAS_VIAS', 'BSADE_NORMAM_2D'],
                                'modelo_slug' => 'anexo-2b-bsade',
                            ],
                            [
                                'anexo' => '2-K',
                                'nome' => 'Autorização para transferência de propriedade',
                                'descricao' => 'Comprovação da transferência junto à Marinha (com assinaturas reconhecidas ou assinatura digital).',
                                'checklist_codigos' => ['COMPROVACAO_TRANSFERENCIA_2K'],
                            ],
                            [
                                'anexo' => '2-I',
                                'nome' => 'Comunicado de venda',
                                'descricao' => 'Comunicação de venda da embarcação conforme NORMAM-211.',
                            ],
                            [
                                'anexo' => '2-H',
                                'nome' => 'Declaração de extravio (TIE / TIEM)',
                                'descricao' => 'Para perda ou roubo do documento da embarcação; segunda via do TIE/TIEM.',
                                'checklist_codigos' => ['DECL_EXTRAVIO_2H'],
                                'modelo_slug' => 'anexo-2h-normam211',
                            ],
                        ],
                    ],
                    [
                        'id' => 'cha_211',
                        'titulo' => 'Documentação para amadores (CHA)',
                        'itens' => [
                            [
                                'anexo' => '5-H',
                                'nome' => 'Requerimento CHA',
                                'descricao' => 'Requerimento do interessado para serviços de habilitação amador (NORMAM-211).',
                                'checklist_codigos' => ['CHA_REQ_ANEXO_5H', 'CHA_REQ_ANEXO_5H_OCORRENCIA'],
                                'modelo_slug' => 'anexo-5h',
                            ],
                            [
                                'anexo' => '5-D',
                                'nome' => 'Declaração de extravio ou dano (CHA)',
                                'descricao' => 'Segunda via em caso de perda ou deterioração física da habilitação (categorias abrangidas pela NORMAM-211).',
                                'checklist_codigos' => ['CHA_DECL_EXTRAVIO_DANO_ANEXO_5D', 'CHA_OU_DECL_EXTRAVIO_5D'],
                                'modelo_slug' => 'anexo-5d',
                            ],
                        ],
                    ],
                    [
                        'id' => 'admin_211',
                        'titulo' => 'Documentos administrativos e de identificação (NORMAM-211)',
                        'itens' => [
                            [
                                'anexo' => '2-G',
                                'nome' => 'Declaração de residência',
                                'descricao' => 'Quando não há comprovante de endereço em nome próprio (esporte e recreio).',
                                'checklist_codigos' => [Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP],
                                'modelo_slug' => 'anexo-2g',
                            ],
                            [
                                'anexo' => '2-C',
                                'nome' => 'Requerimento do interessado',
                                'descricao' => 'Formulário geral para petições à Capitania, Delegacia ou Agência.',
                                'checklist_codigos' => ['TIE_REQ_INTERESSADO_ANEXO_2C_211', 'REQ_NORMAM_2C'],
                                'modelo_slug' => 'anexo-2c-normam211',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Texto contínuo útil para contexto em ajuda interna, prompts ou documentação gerada.
     */
    public static function textoResumo(): string
    {
        $blocos = [];
        foreach (self::estrutura() as $norma => $meta) {
            $linhas = [$meta['titulo']];
            foreach ($meta['grupos'] as $grupo) {
                $linhas[] = $grupo['titulo'].':';
                foreach ($grupo['itens'] as $item) {
                    $linhas[] = '  — Anexo '.$item['anexo'].' — '.$item['nome'].': '.$item['descricao'];
                }
            }
            $blocos[] = implode("\n", $linhas);
        }

        return implode("\n\n", $blocos);
    }

    /**
     * Localiza o anexo catalogado a partir de um código de item de checklist (se houver correspondência explícita).
     *
     * @return array{norma: string, anexo: string, nome: string, descricao: string, modelo_slug?: string}|null
     */
    public static function entradaPorCodigoChecklist(string $codigo): ?array
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return null;
        }

        foreach (self::estrutura() as $norma => $meta) {
            foreach ($meta['grupos'] as $grupo) {
                foreach ($grupo['itens'] as $item) {
                    $codigos = $item['checklist_codigos'] ?? [];
                    if ($codigos !== [] && in_array($codigo, $codigos, true)) {
                        $out = [
                            'norma' => $norma,
                            'anexo' => $item['anexo'],
                            'nome' => $item['nome'],
                            'descricao' => $item['descricao'],
                        ];
                        if (isset($item['modelo_slug'])) {
                            $out['modelo_slug'] = $item['modelo_slug'];
                        }

                        return $out;
                    }
                }
            }
        }

        return null;
    }
}
