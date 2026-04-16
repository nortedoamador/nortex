<?php

namespace App\Support;

use App\Models\AulaNautica;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmpresaAtestadoNormamDuracao;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Dados para o PDF do Anexo 5-E (ARA). Ordem dos itens do plano = ordem das linhas no HTML pdf24.
 *
 * @phpstan-type Row array{item_key: string, duracao_minutos: int|null, tempo_label: string}
 */
final class AulaNauticaAraPdfData
{
    /**
     * Ordem exata das linhas DATA/TEMPO no modelo pdf24 (12 teóricos + 8 práticos).
     *
     * @var list<string>
     */
    public const PLANO_ITEM_KEYS_ORDER = [
        'ara_t_01', 'ara_t_02', 'ara_t_03', 'ara_t_04',
        'ara_t_05', 'ara_t_06', 'ara_t_07', 'ara_t_08', 'ara_t_09', 'ara_t_10', 'ara_t_11', 'ara_t_12',
        'ara_p_01', 'ara_p_02', 'ara_p_03', 'ara_p_04', 'ara_p_05', 'ara_p_06', 'ara_p_07', 'ara_p_08',
    ];

    /**
     * Nome do diretor / responsável: ajustar quando existir campo definitivo na base ou config.
     * Por agora: `config('nortex.ara_pdf_diretor_nome')`.
     */
    public static function resolveDiretorNome(?Empresa $empresa): string
    {
        // Futuro: preencher a partir de $empresa quando existir coluna/campo dedicado.
        $empresa?->getKey();
        $nome = config('nortex.ara_pdf_diretor_nome');

        return is_string($nome) ? trim($nome) : '';
    }

    /**
     * @param  Collection<string, EmpresaAtestadoNormamDuracao>  $duracoesPorItem keyed by item_key
     * @return array<string, mixed>
     */
    public static function build(AulaNautica $aula, Cliente $aluno, Collection $duracoesPorItem): array
    {
        $empresa = $aula->empresa ?? Empresa::query()->find($aula->empresa_id);
        $nomeEscola = $empresa?->nome ?? '—';

        $ei = $aula->escolaInstrutores->first();
        $insCliente = $ei?->cliente;
        $insUser = $aula->instrutores->first();

        $instrutorNome = self::nomeInstrutor($insCliente, $insUser);
        $instrutorRg = self::textoOuTraco($insCliente?->documento_identidade_numero ?? $insCliente?->rg);
        $instrutorOrgao = self::textoOuTraco($insCliente?->orgao_emissor);
        $instrutorEmissaoRg = self::formatarData($insCliente?->data_emissao_rg);
        $instrutorCpf = $insCliente?->cpfFormatado() ?? '—';
        $instrutorChaNumero = self::textoOuTraco($ei?->cha_numero);
        $instrutorChaCategoria = self::textoOuTraco($ei?->cha_categoria);

        $plano = self::montarPlano($duracoesPorItem);
        $totalMin = $plano['total_minutos'];
        $horasTreinamentoLabel = self::rotuloHorasTreinamento($totalMin);

        return [
            'data_aula' => self::formatarData($aula->data_aula),
            'escola_nome' => $nomeEscola,
            'diretor_nome' => self::resolveDiretorNome($empresa),
            'instrutor_nome' => $instrutorNome,
            'instrutor_rg' => $instrutorRg,
            'instrutor_orgao_rg' => $instrutorOrgao,
            'instrutor_emissao_rg' => $instrutorEmissaoRg,
            'instrutor_cpf' => $instrutorCpf,
            'instrutor_cha_numero' => $instrutorChaNumero,
            'instrutor_cha_categoria' => $instrutorChaCategoria,
            'aluno_nome' => self::textoOuTraco($aluno->nome),
            'aluno_cpf' => $aluno->cpfFormatado() ?? '—',
            'aluno_rg' => self::textoOuTraco($aluno->documento_identidade_numero ?? $aluno->rg),
            'aluno_orgao_rg' => self::textoOuTraco($aluno->orgao_emissor),
            'aluno_emissao_rg' => self::formatarData($aluno->data_emissao_rg),
            'horas_treinamento_label' => $horasTreinamentoLabel,
            /** @var list<string> */
            'tempos' => $plano['tempos_labels'],
            'plano_rows' => $plano['rows'],
            'total_minutos_normam' => $totalMin,
        ];
    }

    /**
     * Pré-visualização do modelo global (sem {@see AulaNautica}): aluno = cliente escolhido,
     * tempos do plano = durações NORMAM da empresa, restantes marcadores.
     *
     * @return array<string, mixed>
     */
    public static function buildPreviewForCliente(Cliente $cliente, int $empresaId, \DateTimeInterface $dataReferencia): array
    {
        $empresa = Empresa::query()->find($empresaId);
        $duracoes = EmpresaAtestadoNormamDuracao::query()
            ->where('empresa_id', $empresaId)
            ->where('programa', AulaCurriculoNormam::PROGRAMA_ARA)
            ->get()
            ->keyBy('item_key');

        $plano = self::montarPlano($duracoes);
        $totalMin = $plano['total_minutos'];

        return [
            'data_aula' => self::formatarData($dataReferencia),
            'escola_nome' => $empresa?->nome ?? '—',
            'diretor_nome' => self::resolveDiretorNome($empresa),
            'instrutor_nome' => '—',
            'instrutor_rg' => '—',
            'instrutor_orgao_rg' => '—',
            'instrutor_emissao_rg' => '—',
            'instrutor_cpf' => '—',
            'instrutor_cha_numero' => '—',
            'instrutor_cha_categoria' => '—',
            'aluno_nome' => self::textoOuTraco($cliente->nome),
            'aluno_cpf' => $cliente->cpfFormatado() ?? '—',
            'aluno_rg' => self::textoOuTraco($cliente->documento_identidade_numero ?? $cliente->rg),
            'aluno_orgao_rg' => self::textoOuTraco($cliente->orgao_emissor),
            'aluno_emissao_rg' => self::formatarData($cliente->data_emissao_rg),
            'horas_treinamento_label' => self::rotuloHorasTreinamento($totalMin),
            /** @var list<string> */
            'tempos' => $plano['tempos_labels'],
            'plano_rows' => $plano['rows'],
            'total_minutos_normam' => $totalMin,
        ];
    }

    /**
     * @return array{rows: list<Row>, tempos_labels: list<string>, total_minutos: int}
     */
    private static function montarPlano(Collection $duracoesPorItem): array
    {
        $rows = [];
        $tempos = [];
        $total = 0;

        foreach (self::PLANO_ITEM_KEYS_ORDER as $itemKey) {
            /** @var EmpresaAtestadoNormamDuracao|null $row */
            $row = $duracoesPorItem->get($itemKey);
            $min = $row?->duracao_minutos;
            if (is_numeric($min)) {
                $total += (int) $min;
            }
            $label = self::formatarDuracaoPlano($min);
            $tempos[] = $label;
            $rows[] = [
                'item_key' => $itemKey,
                'duracao_minutos' => is_numeric($min) ? (int) $min : null,
                'tempo_label' => $label,
            ];
        }

        return ['rows' => $rows, 'tempos_labels' => $tempos, 'total_minutos' => $total];
    }

    private static function nomeInstrutor(?Cliente $insCliente, ?User $insUser): string
    {
        $n = $insCliente?->nome;
        if (is_string($n) && trim($n) !== '') {
            return trim($n);
        }
        $u = $insUser?->name;
        if (is_string($u) && trim($u) !== '') {
            return trim($u);
        }

        return '—';
    }

    private static function textoOuTraco(?string $v): string
    {
        if ($v === null) {
            return '—';
        }
        $t = trim($v);

        return $t === '' ? '—' : $t;
    }

    private static function formatarData(mixed $data): string
    {
        if ($data === null) {
            return '—';
        }
        try {
            if ($data instanceof \DateTimeInterface) {
                return $data->format('d/m/Y');
            }
        } catch (\Throwable) {
            return '—';
        }

        return '—';
    }

    private static function formatarDuracaoPlano(mixed $minutos): string
    {
        if (! is_numeric($minutos) || (int) $minutos <= 0) {
            return '—';
        }
        $m = (int) $minutos;
        if ($m < 60) {
            return $m.' min';
        }
        $h = intdiv($m, 60);
        $r = $m % 60;

        return $r === 0 ? $h.'h' : $h.'h'.str_pad((string) $r, 2, '0', STR_PAD_LEFT).'min';
    }

    private static function rotuloHorasTreinamento(int $totalMinutos): string
    {
        if ($totalMinutos <= 0) {
            return '—';
        }
        $h = intdiv($totalMinutos, 60);
        $m = $totalMinutos % 60;
        if ($m === 0 && $h > 0 && $h <= 24) {
            $ext = self::inteiroPorExtensoPt($h);

            return $h.' ('.$ext.')';
        }
        if ($m > 0) {
            return $h.'h'.str_pad((string) $m, 2, '0', STR_PAD_LEFT).'min';
        }

        return (string) $h;
    }

    private static function inteiroPorExtensoPt(int $n): string
    {
        $map = [
            1 => 'UM', 2 => 'DOIS', 3 => 'TRES', 4 => 'QUATRO', 5 => 'CINCO',
            6 => 'SEIS', 7 => 'SETE', 8 => 'OITO', 9 => 'NOVE', 10 => 'DEZ',
            11 => 'ONZE', 12 => 'DOZE', 13 => 'TREZE', 14 => 'CATORZE', 15 => 'QUINZE',
            16 => 'DEZESSEIS', 17 => 'DEZESSETE', 18 => 'DEZOITO', 19 => 'DEZENOVE', 20 => 'VINTE',
            21 => 'VINTE E UM', 22 => 'VINTE E DOIS', 23 => 'VINTE E TRES', 24 => 'VINTE E QUATRO',
        ];

        return $map[$n] ?? (string) $n;
    }
}
