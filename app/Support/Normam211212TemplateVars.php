<?php

namespace App\Support;

use App\Enums\EmbarcacaoAreaNavegacao;
use App\Models\Cliente;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\Processo;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Variáveis legadas ($nome, $cpf, …) para modelos NORMAM 211/212.
 * Usadas com Blade::render(): um @include que define @php não partilha escopo com o resto do template.
 */
final class Normam211212TemplateVars
{
    /**
     * Chaves escalares disponíveis nos modelos Blade (extract + $variáveis), excluindo os modelos `c` e `e`.
     * Manter alinhado com o array devolvido por {@see self::variablesFor()}.
     *
     * @return list<string>
     */
    public static function bladeBindingKeyList(): array
    {
        return [
            'nome', 'cpf', 'rg', 'orgao', 'endereco', 'numero', 'bairro', 'cidade', 'uf', 'cep',
            'complemento', 'apartamento', 'telefone', 'tel', 'celular', 'email', 'fax',
            'nacionalidade', 'naturalidade', 'dt_emissao', 'nome_embarcacao', 'inscricao',
            'comprimento', 'casco', 'numero_casco', 'classificacao', 'tipo', 'construtor', 'ano',
            'tripulantes', 'passageiros', 'area_navegacao', 'boca', 'pontal', 'calado', 'contorno', 'material_casco', 'potmax_casco',
            'arq_bruta', 'arq_liquida', 'marca_motor', 'potmax_motor', 'numero_motor',
            'marca_motor2', 'marca_motor3', 'potmax_motor2', 'potmax_motor3', 'numero_motor2', 'numero_motor3',
            'numero_nf', 'dt_nf', 'local_nf', 'vendedor_nf', 'documento_vendedor_nf',
            'dt_emissao_fmt', 'data', 'nascimento', 'identidade', 'endereco_completo', 'telefone_email_linha',
            'cha_numero', 'cha_dt_emissao_fmt', 'categoria_cha',
            'cha_emissao_dia', 'cha_emissao_mes', 'cha_emissao_ano',
            'rg_dia', 'rg_mes', 'rg_ano',
            'decl_dia', 'decl_mes', 'decl_ano',
            'local_declaracao', 'numero_compl', 'cidade_uf_linha',
            'ocorrencia', 'observacao', 'novo_nome_embarcacao', 'novo_nome_embarcacao2', 'novo_nome_embarcacao3',
            'complemento1', 'complemento2', 'nome_embarcacao_parte1', 'nome_embarcacao_parte2',
            'jurisdicao_destino',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function variablesFor(Cliente $cliente, ?Embarcacao $embarcacao = null): array
    {
        $c = $cliente;
        $e = $embarcacao;

        $nome = $c->nome ?? '';
        $cpf = $c->cpfFormatado() ?? $c->cpf ?? '';
        $rg = $c->rg ?? '';
        $orgao = $c->orgao_emissor ?? '';
        $endereco = $c->endereco ?? '';
        $numero = $c->numero ?? '';
        $bairro = $c->bairro ?? '';
        $cidade = $c->cidade ?? '';
        $uf = $c->uf ?? '';
        $cep = $c->cepFormatado() ?? $c->cep ?? '';
        $complemento = $c->complemento ?? '';
        $apartamento = $c->apartamento ?? '';
        $telefone = $c->telefoneFormatado() ?? $c->telefone ?? '';
        $tel = $telefone;
        $celular = $c->celularFormatado() ?? $c->celular ?? '';
        $email = $c->email ?? '';
        $fax = '';
        $nacionalidade = $c->nacionalidade ?? '';
        $naturalidade = $c->naturalidade ?? '';
        $dt_emissao = $c->data_emissao_rg ? $c->data_emissao_rg->timestamp : null;

        $nome_embarcacao = $e?->nome ?? '';
        $inscricao = $e?->inscricao ?? '';
        $comprimento = $e?->comprimento_m ?? $e?->comprimento ?? '';
        $casco = $e?->numero_casco ?? '';
        $numero_casco = $casco;
        $classificacao = $e?->atividade ?? '';
        $tipo = $e?->tipo ?? '';
        $construtor = $e?->construtor ?? '';
        $ano = $e?->ano_fabricacao ?? $e?->ano_construcao ?? '';
        $tripulantes = $e?->tripulantes ?? '';
        $passageiros = $e?->passageiros ?? '';
        $areaNav = $e?->area_navegacao;
        $area_navegacao = '';
        if ($areaNav instanceof EmbarcacaoAreaNavegacao) {
            $area_navegacao = mb_strtoupper($areaNav->label(), 'UTF-8');
        }
        $boca = $e?->boca_m ?? $e?->boca ?? '';
        $pontal = $e?->pontal_m ?? $e?->pontal ?? '';
        $calado = $e?->calado ?? '';
        $contorno = $e?->contorno ?? '';
        $material_casco = $e?->material_casco ?? '';
        $potmax_casco = $e?->potencia_maxima_casco ?? '';
        $arq_bruta = $e?->arqueacao_bruta ?? '';
        $arq_liquida = $e?->arqueacao_liquida ?? '';
        $marca_motor = $e?->marca_motor ?? '';
        $potmax_motor = $e?->potencia_maxima_motor ?? '';
        $numero_motor = $e?->numero_motor ?? '';
        $marca_motor2 = '';
        $marca_motor3 = '';
        $potmax_motor2 = '';
        $potmax_motor3 = '';
        $numero_motor2 = '';
        $numero_motor3 = '';

        $numero_nf = $e?->nf_numero ?? '';
        $dt_nf = '';
        if ($e && filled($e->nf_data)) {
            try {
                $dt_nf = Carbon::parse($e->nf_data)->format('d/m/Y');
            } catch (\Throwable) {
                $dt_nf = (string) $e->nf_data;
            }
        }
        $local_nf = $e?->nf_local ?? '';
        $vendedor_nf = $e?->nf_vendedor ?? '';
        $documento_vendedor_nf = $e?->nf_documento_vendedor ?? '';

        $dt_emissao_fmt = $c->data_emissao_rg ? $c->data_emissao_rg->format('d/m/Y') : '';

        $data = Carbon::now()->format('d/m/Y');

        $nascimento = $c->data_nascimento ? $c->data_nascimento->format('d/m/Y') : '';

        $identidade = '';
        if (filled($c->rg)) {
            $identidade = (string) $c->rg;
            if (filled($c->orgao_emissor)) {
                $identidade .= ' — '.(string) $c->orgao_emissor;
            }
        } elseif (filled($c->documento_identidade_numero)) {
            $tipoDoc = trim((string) ($c->documento_identidade_tipo ?? ''));
            $identidade = trim($tipoDoc.' '.(string) $c->documento_identidade_numero);
        } elseif (filled($c->numero_cnh)) {
            $identidade = 'CNH '.(string) $c->numero_cnh;
        }

        $endereco_completo = collect([
            trim(implode(', ', array_filter([(string) ($c->endereco ?? ''), (string) ($c->numero ?? '')]))),
            filled($c->complemento) ? (string) $c->complemento : null,
            filled($c->apartamento) ? 'Apto '.(string) $c->apartamento : null,
            filled($c->bairro) ? (string) $c->bairro : null,
            trim(implode(' / ', array_filter([(string) ($c->cidade ?? ''), (string) ($c->uf ?? '')]))),
            $c->cepFormatado() ?? ($c->cep !== null && $c->cep !== '' ? (string) $c->cep : null),
        ])->filter(static fn ($v) => filled($v))->implode(', ');

        $celTrim = trim((string) $celular);
        $telTrim = trim((string) $telefone);
        $telefoneOuCelular = $celTrim !== '' ? $celTrim : $telTrim;
        $emailTrim = trim((string) $email);

        $telefone_email_linha = collect([$telefoneOuCelular, $emailTrim])
            ->filter(static fn ($v) => $v !== '')
            ->implode(', ');

        $chaRegistro = Habilitacao::query()
            ->where('empresa_id', $c->empresa_id)
            ->where('cliente_id', $c->id)
            ->whereNotNull('numero_cha')
            ->where('numero_cha', '!=', '')
            ->orderByDesc('updated_at')
            ->first();

        $cha_numero = $chaRegistro?->numero_cha ?? '';
        $categoria_cha = $chaRegistro?->categoria ?? '';

        $cha_dt_emissao_fmt = '';
        if ($chaRegistro !== null && filled($chaRegistro->data_emissao ?? null)) {
            try {
                $cha_dt_emissao_fmt = $chaRegistro->data_emissao->format('d/m/Y');
            } catch (\Throwable) {
                $cha_dt_emissao_fmt = '';
            }
        }

        $ocorrencia = '';

        $observacao = '';
        $novo_nome_embarcacao = $e?->nome_casco ?? '';
        $novo_nome_embarcacao2 = '';
        $novo_nome_embarcacao3 = '';

        $complementoStr = (string) $complemento;
        $complemento1 = Str::substr($complementoStr, 0, 6);
        $complemento2 = Str::substr($complementoStr, 6);

        $nomeEmbStr = (string) $nome_embarcacao;
        $nome_embarcacao_parte1 = Str::substr($nomeEmbStr, 0, 15);
        $nome_embarcacao_parte2 = Str::substr($nomeEmbStr, 15);

        // Mesma lógica que resources/views/documento-modelos/partials/anexo-5d-variaveis.blade.php
        // (obrigatório aqui: @include + @php não partilha escopo com o resto do modelo em Blade::render).
        $rg = filled($rg) ? $rg : ($c->rg ?? $c->documento_identidade_numero ?? '');

        $rg_dia = '';
        $rg_mes = '';
        $rg_ano = '';
        if (filled($dt_emissao_fmt)
            && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim((string) $dt_emissao_fmt), $nx5d_m)) {
            $rg_dia = $nx5d_m[1];
            $rg_mes = $nx5d_m[2];
            $rg_ano = $nx5d_m[3];
        }

        $cha_emissao_dia = '';
        $cha_emissao_mes = '';
        $cha_emissao_ano = '';
        if (filled($cha_dt_emissao_fmt)
            && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim((string) $cha_dt_emissao_fmt), $nx5d_c)) {
            $cha_emissao_dia = $nx5d_c[1];
            $cha_emissao_mes = $nx5d_c[2];
            $cha_emissao_ano = $nx5d_c[3];
        }

        $hojeRef = Carbon::now();
        $decl_dia = $hojeRef->format('d');
        $decl_mes = $hojeRef->format('m');
        $decl_ano = $hojeRef->format('Y');

        $local_declaracao = $cidade ?? '';

        $jurisdicao_destino = '';
        $jurisdicaoProc = Processo::query()
            ->where('empresa_id', $c->empresa_id)
            ->where('cliente_id', $c->id)
            ->whereNotNull('jurisdicao')
            ->where('jurisdicao', '!=', '')
            ->orderByDesc('updated_at')
            ->value('jurisdicao');
        if (filled($jurisdicaoProc)) {
            $j = trim((string) $jurisdicaoProc);
            if ($j !== '') {
                $jurisdicao_destino = str_starts_with(Str::lower($j), 'à ')
                    ? $j
                    : 'À '.$j;
            }
        }

        $numero_compl = trim(trim((string) ($numero ?? '')).' '.trim((string) ($complemento ?? '')));
        $cidade_uf_linha = trim(($cidade ?? '').(($uf ?? '') !== '' ? ' / '.$uf : ''));

        return [
            'c' => $c,
            'e' => $e,
            'nome' => $nome,
            'cpf' => $cpf,
            'rg' => $rg,
            'orgao' => $orgao,
            'endereco' => $endereco,
            'numero' => $numero,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'uf' => $uf,
            'cep' => $cep,
            'complemento' => $complemento,
            'apartamento' => $apartamento,
            'telefone' => $telefone,
            'tel' => $tel,
            'celular' => $celular,
            'email' => $email,
            'fax' => $fax,
            'nacionalidade' => $nacionalidade,
            'naturalidade' => $naturalidade,
            'dt_emissao' => $dt_emissao,
            'nome_embarcacao' => $nome_embarcacao,
            'inscricao' => $inscricao,
            'comprimento' => $comprimento,
            'casco' => $casco,
            'numero_casco' => $numero_casco,
            'classificacao' => $classificacao,
            'tipo' => $tipo,
            'construtor' => $construtor,
            'ano' => $ano,
            'tripulantes' => $tripulantes,
            'passageiros' => $passageiros,
            'area_navegacao' => $area_navegacao,
            'boca' => $boca,
            'pontal' => $pontal,
            'calado' => $calado,
            'contorno' => $contorno,
            'material_casco' => $material_casco,
            'potmax_casco' => $potmax_casco,
            'arq_bruta' => $arq_bruta,
            'arq_liquida' => $arq_liquida,
            'marca_motor' => $marca_motor,
            'potmax_motor' => $potmax_motor,
            'numero_motor' => $numero_motor,
            'marca_motor2' => $marca_motor2,
            'marca_motor3' => $marca_motor3,
            'potmax_motor2' => $potmax_motor2,
            'potmax_motor3' => $potmax_motor3,
            'numero_motor2' => $numero_motor2,
            'numero_motor3' => $numero_motor3,
            'numero_nf' => $numero_nf,
            'dt_nf' => $dt_nf,
            'local_nf' => $local_nf,
            'vendedor_nf' => $vendedor_nf,
            'documento_vendedor_nf' => $documento_vendedor_nf,
            'dt_emissao_fmt' => $dt_emissao_fmt,
            'data' => $data,
            'nascimento' => $nascimento,
            'identidade' => $identidade,
            'endereco_completo' => $endereco_completo,
            'telefone_email_linha' => $telefone_email_linha,
            'cha_numero' => $cha_numero,
            'categoria_cha' => $categoria_cha,
            'cha_dt_emissao_fmt' => $cha_dt_emissao_fmt,
            'cha_emissao_dia' => $cha_emissao_dia,
            'cha_emissao_mes' => $cha_emissao_mes,
            'cha_emissao_ano' => $cha_emissao_ano,
            'rg_dia' => $rg_dia,
            'rg_mes' => $rg_mes,
            'rg_ano' => $rg_ano,
            'decl_dia' => $decl_dia,
            'decl_mes' => $decl_mes,
            'decl_ano' => $decl_ano,
            'local_declaracao' => $local_declaracao,
            'jurisdicao_destino' => $jurisdicao_destino,
            'numero_compl' => $numero_compl,
            'cidade_uf_linha' => $cidade_uf_linha,
            'ocorrencia' => $ocorrencia,
            'observacao' => $observacao,
            'novo_nome_embarcacao' => $novo_nome_embarcacao,
            'novo_nome_embarcacao2' => $novo_nome_embarcacao2,
            'novo_nome_embarcacao3' => $novo_nome_embarcacao3,
            'complemento1' => $complemento1,
            'complemento2' => $complemento2,
            'nome_embarcacao_parte1' => $nome_embarcacao_parte1,
            'nome_embarcacao_parte2' => $nome_embarcacao_parte2,
        ];
    }
}
