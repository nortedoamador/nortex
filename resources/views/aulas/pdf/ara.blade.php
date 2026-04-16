@php
    /** @var \Illuminate\Support\Collection<string, \App\Models\EmpresaAtestadoNormamDuracao> $duracoesMap */
    /** @var array{teorico: list<array{key: string, label: string}>, pratico: list<array{key: string, label: string}>} $curriculoAra */
    $instrutorEt = $aula->escolaInstrutores->first();
    $instrutorCli = $instrutorEt?->cliente;
    $nomeEscola = $escola?->nome ?? '—';
    $nomeDiretor = $escola?->diretor?->nome ?? '—';
    $nomeInstrutor = $instrutorCli?->nome ?? '—';
    $rgInstrutor = $instrutorCli?->rg ?? '—';
    $orgaoRgInstrutor = $instrutorCli?->orgao_emissor ?? '—';
    $dataRgInstrutor = $instrutorCli?->data_emissao_rg?->format('d/m/Y') ?? '—';
    $cpfInstrutor = $instrutorCli?->cpfFormatado() ?? ($instrutorCli?->cpf ?? '—');
    $chaNumeroInstrutor = $instrutorEt?->cha_numero ?? '—';
    $chaCategoriaInstrutor = $instrutorEt?->cha_categoria ?? '—';
    $dataAulaFmt = $aula->data_aula?->format('d/m/Y') ?? '—';
@endphp
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 15px; margin: 0 0 10px; }
        h2 { font-size: 12px; margin: 14px 0 6px; }
        h3 { font-size: 11px; margin: 12px 0 6px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 12px; }
        th, td { border: 1px solid #999; padding: 5px 6px; vertical-align: top; text-align: left; }
        th { background: #f0f0f0; width: 38%; }
        .muted { color: #555; font-size: 10px; }
        .cert { border: 1px solid #333; padding: 12px; margin: 0 0 16px; page-break-inside: avoid; }
        .cert-last { page-break-after: auto; }
        .cert:not(.cert-last) { page-break-after: always; }
        .sub { font-size: 10px; color: #444; margin-top: 2px; }
    </style>
</head>
<body>
    <h1>Atestado ARA (Arrais-Amador) — lote</h1>
    <p class="muted">Um bloco por aluno vinculado à aula. Instrutor: primeiro instrutor ETN associado à aula (quando houver mais de um).</p>

    @forelse ($aula->alunos as $index => $aluno)
        @php
            $isLast = $index === $aula->alunos->count() - 1;
            $nomeAluno = $aluno->nome ?? '—';
            $cpfAluno = $aluno->cpfFormatado() ?? ($aluno->cpf ?? '—');
            $rgAluno = $aluno->rg ?? '—';
            $orgaoRgAluno = $aluno->orgao_emissor ?? '—';
            $dataRgAluno = $aluno->data_emissao_rg?->format('d/m/Y') ?? '—';
        @endphp
        <div class="cert {{ $isLast ? 'cert-last' : '' }}">
            <h2>Atestado — {{ $nomeAluno }}</h2>

            <h3>Escola e responsável</h3>
            <table>
                <tr>
                    <th>Nome da escola / estabelecimento náutico</th>
                    <td>{{ $nomeEscola }}</td>
                </tr>
                <tr>
                    <th>Nome do diretor / responsável</th>
                    <td>{{ $nomeDiretor }}</td>
                </tr>
            </table>

            <h3>Instrutor</h3>
            <table>
                <tr>
                    <th>Nome do instrutor</th>
                    <td>{{ $nomeInstrutor }}</td>
                </tr>
                <tr>
                    <th>RG do instrutor</th>
                    <td>{{ $rgInstrutor }}</td>
                </tr>
                <tr>
                    <th>Órgão emissor do RG do instrutor</th>
                    <td>{{ $orgaoRgInstrutor }}</td>
                </tr>
                <tr>
                    <th>Data de emissão do RG do instrutor</th>
                    <td>{{ $dataRgInstrutor }}</td>
                </tr>
                <tr>
                    <th>CPF do instrutor</th>
                    <td>{{ $cpfInstrutor }}</td>
                </tr>
                <tr>
                    <th>Categoria da CHA do instrutor</th>
                    <td>{{ $chaCategoriaInstrutor }}</td>
                </tr>
                <tr>
                    <th>Número da CHA do instrutor</th>
                    <td>{{ $chaNumeroInstrutor }}</td>
                </tr>
            </table>

            <h3>Aluno</h3>
            <table>
                <tr>
                    <th>Nome completo do aluno</th>
                    <td>{{ $nomeAluno }}</td>
                </tr>
                <tr>
                    <th>CPF do aluno</th>
                    <td>{{ $cpfAluno }}</td>
                </tr>
                <tr>
                    <th>RG do aluno</th>
                    <td>{{ $rgAluno }}</td>
                </tr>
                <tr>
                    <th>Órgão emissor do RG do aluno</th>
                    <td>{{ $orgaoRgAluno }}</td>
                </tr>
                <tr>
                    <th>Data de emissão do RG do aluno</th>
                    <td>{{ $dataRgAluno }}</td>
                </tr>
                <tr>
                    <th>Data da aula</th>
                    <td>{{ $dataAulaFmt }}</td>
                </tr>
            </table>

            <h3>Planejamento de aula (ARA) — tempos cadastrados (minutos)</h3>
            <p class="sub">Conteúdos fixos NORMAM; durações definidas em Escola Náutica → Atestados → ARA.</p>

            <h3>Conteúdo teórico</h3>
            <table>
                <thead>
                    <tr>
                        <th>Conteúdo</th>
                        <th style="width: 22%;">Duração</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curriculoAra['teorico'] as $item)
                        @php
                            $min = $duracoesMap->get($item['key'])?->duracao_minutos;
                        @endphp
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td>{{ $min !== null ? $min.' min' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3>Conteúdo prático</h3>
            <table>
                <thead>
                    <tr>
                        <th>Conteúdo</th>
                        <th style="width: 22%;">Duração</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curriculoAra['pratico'] as $item)
                        @php
                            $min = $duracoesMap->get($item['key'])?->duracao_minutos;
                        @endphp
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td>{{ $min !== null ? $min.' min' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="muted">Nº ofício: {{ $aula->numero_oficio }} | Local: {{ $aula->local }}</p>
        </div>
    @empty
        <p class="muted">Nenhum aluno vinculado a esta aula.</p>
    @endforelse
</body>
</html>
