<?php

namespace App\Support;

/**
 * Catálogo “fonte de verdade” dos tipos de serviço (processos) de embarcação.
 *
 * Mantém a ordem exigida no dropdown e para seeding/migração.
 */
final class EmbarcacaoTipoServicoCatalogo
{
    /**
     * @return list<array{slug: string, nome: string}>
     */
    public static function listaOrdenada(): array
    {
        // Ordem obrigatória (deduplicada) conforme especificação do cliente.
        return [
            [
                'slug' => 'tietie-inscricao-ate-12m',
                'nome' => 'TIETIE (TITULO DE INSCRICAO DE EMBARCACAO) - EMBARCACAO COM COMPRIMENTO IGUAL OU MENOR QUE 12 METROS - INSCRICAO',
            ],
            [
                'slug' => 'tie-inscricao-navegacao-interior-ab100',
                'nome' => 'TIE (TITULO DE INSCRICAO DE EMBARCACAO) - NAVEGACAO INTERIOR - AB MENOR OU IGUAL A 100, EXCETO AS MIUDAS - INSCRICAO',
            ],
            [
                'slug' => 'tie-inscricao-nav-mar-aberto-ab100',
                'nome' => 'TIE (TITULO DE INSCRICAO DE EMBARCACAO) - NAV. MAR ABERTO - AB MENOR OU IGUAL A 100, EXCETO AS MIUDAS - INSCRICAO',
            ],
            [
                'slug' => 'tie-inscricao-moto-aquatica',
                'nome' => 'TIE (TITULO DE INSCRICAO DE EMBARCACAO) - MOTO AQUATICA - INSCRICAO',
            ],
            [
                'slug' => 'tie-renovacao',
                'nome' => 'TIE (TITULO DE INSCRICAO DE EMBARCACAO) - RENOVAÇÃO',
            ],
            [
                'slug' => 'tie-alteracao-dados-embarcacao-cpdlag',
                'nome' => 'TIE - ALTERACAO DE CARACTERISTICAS, ALTERACAO DA RAZAO SOCIAL OU MUDANCA DE ENDERECO DO PROPRIETARIO - EMBARCACAO INSCRITA NAS CP/DL/AG',
            ],
            [
                'slug' => 'tie-transferencia-propriedade-er-cpdlag',
                'nome' => 'TIE - TRANSFERENCIA DE PROPRIEDADE DE EMBARCACAO - ESPORTE E RECREIO - INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-transferencia-propriedade-interior-cpdlag',
                'nome' => 'TIE - TRANSFERENCIA DE PROPRIEDADE DE EMBARCACAO - NAVEGACAO INTERIOR - INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-transferencia-propriedade-mar-aberto-cpdlag',
                'nome' => 'TIE - TRANSFERENCIA DE PROPRIEDADE DE EMBARCACAO - NAV. MAR ABERTO - INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-transferencia-propriedade-moto-aquatica-cpdlag',
                'nome' => 'TIE - TRANSFERENCIA DE PROPRIEDADE DE MOTO AQUATICA - INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-transferencia-jurisdicao-embarcacao-cpdlag',
                'nome' => 'TIE - TRANSFERENCIA DE JURISDICAO DE EMBARCACAO - INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-transferencia-jurisdicao-moto-aquatica-cpdlag',
                'nome' => 'TIE - TRANSFERENCIA DE JURISDICAO DE MOTO AQUATICA - INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-registro-onus-averbacoes-cpdlag',
                'nome' => 'TIE - REGISTRO DE ONUS E AVERBACOES - EMBARCACAO INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'tie-cancelamento-onus-averbacoes-cpdlag',
                'nome' => 'TIE - CANCELAMENTO DO REGISTRO DE ONUS E AVERBACOES - EMBARCACAO INSCRITA NA CP/DL/AG',
            ],
            [
                'slug' => 'dppprpm-registro-er-grande-porte-ab-gt-100',
                'nome' => 'DPP/PRPM - EMBARCACAO DE ESPORTE E RECREIO - COM COMPRIMENTO IGUAL OU MAIOR QUE 24 METROS (GRANDE PORTE) E COM AB MAIOR QUE 100 - REGISTRO',
            ],
            [
                'slug' => 'dppprpm-registro-navegacao-interior-ab-gt-100',
                'nome' => 'DPP/PRPM - EMBARCACAO NAVEGACAO INTERIOR - COM AB MAIOR QUE 100 - REGISTRO',
            ],
            [
                'slug' => 'dppprpm-transferencia-propriedade-er-tm',
                'nome' => 'DPP/PRPM - TRANSFERENCIA DE PROPRIEDADE DE EMBARCACAO - ESPORTE E RECREIO - REGISTRADA NO TM',
            ],
            [
                'slug' => 'dppprpm-transferencia-propriedade-interior-tm',
                'nome' => 'DPP/PRPM - TRANSFERENCIA DE PROPRIEDADE DE EMBARCACAO - NAVEGACAO INTERIOR - REGISTRADA NO TM',
            ],
            [
                'slug' => 'dppprpm-transferencia-propriedade-mar-aberto-tm',
                'nome' => 'DPP/PRPM - TRANSFERENCIA DE PROPRIEDADE DE EMBARCACAO - NAV. MAR ABERTO - REGISTRADA NO TM',
            ],
            [
                'slug' => 'dppprpm-transferencia-jurisdicao-embarcacao-tm',
                'nome' => 'DPP/PRPM - TRANSFERENCIA DE JURISDICAO DE EMBARCACAO - REGISTRADA NO TM',
            ],
            [
                'slug' => 'dppprpm-registro-onus-averbacoes-tm',
                'nome' => 'DPP/PRPM - REGISTRO DE ONUS E AVERBACOES - EMBARCACAO REGISTRADA NO TM',
            ],
            [
                'slug' => 'dppprpm-cancelamento-onus-averbacoes-tm',
                'nome' => 'DPP/PRPM - CANCELAMENTO DO REGISTRO DE ONUS E AVERBACOES - EMBARCACAO REGISTRADA NO TM',
            ],
        ];
    }
}

