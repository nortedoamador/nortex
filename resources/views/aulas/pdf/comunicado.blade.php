<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 12px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; vertical-align: top; }
        th { background: #f2f2f2; text-align: left; }
        .muted { color: #555; }
    </style>
</head>
<body>
    <h1>Comunicado de Aula (Ofício) — Placeholder</h1>
    <p class="muted">Modelo oficial será aplicado quando fornecido.</p>

    <h2>Dados da aula</h2>
    <table>
        <tr>
            <th>Nº Ofício</th>
            <td>{{ $aula->numero_oficio }}</td>
        </tr>
        <tr>
            <th>Data</th>
            <td>{{ optional($aula->data_aula)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Local</th>
            <td>{{ $aula->local }}</td>
        </tr>
        <tr>
            <th>Tipo da aula</th>
            <td>
                @php
                    $tipoLabel = match ((string)($aula->tipo_aula ?? '')) {
                        'teorica' => 'Teórica',
                        'pratica' => 'Prática',
                        'teorica_pratica' => 'Teórica e Prática',
                        default => (string)($aula->tipo_aula ?? '—'),
                    };
                @endphp
                {{ $tipoLabel }}
            </td>
        </tr>
        <tr>
            <th>Horário</th>
            <td>{{ $aula->hora_inicio ? substr($aula->hora_inicio, 0, 5) : '—' }} – {{ $aula->hora_fim ? substr($aula->hora_fim, 0, 5) : '—' }}</td>
        </tr>
    </table>

    <h2>Instrutores</h2>
    @php
        $progLabels = \App\Support\AulaEscolaInstrutorProgramaAtestado::labels();
    @endphp
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>CHA</th>
                <th>Programa (atestado)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($aula->escolaInstrutores as $ei)
                @php $c = $ei->cliente; $p = $ei->pivot->programa_atestado ?? 'ambos'; @endphp
                <tr>
                    <td>{{ $c?->nome ?? '—' }}</td>
                    <td>{{ $c?->cpf ?? '—' }}</td>
                    <td>{{ $ei->cha_numero ?? '—' }}</td>
                    <td>{{ $progLabels[$p] ?? $p }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Alunos</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($aula->alunos as $aluno)
                <tr>
                    <td>{{ $aluno->nome }}</td>
                    <td>{{ $aluno->cpf }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

