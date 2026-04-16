<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 12px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        .muted { color: #555; }
        .card { border: 1px solid #999; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Atestado MTA (lote) — Placeholder</h1>
    <p class="muted">Um atestado por aluno. Modelo oficial será aplicado quando fornecido.</p>

    @if (isset($instrutorEscolaMta) && $instrutorEscolaMta)
        @php $ic = $instrutorEscolaMta->cliente; @endphp
        <div class="card">
            <h2>Instrutor (MTA)</h2>
            <p><strong>Nome:</strong> {{ $ic?->nome ?? '—' }}</p>
            <p><strong>CPF:</strong> {{ $ic?->cpf ?? '—' }}</p>
            <p><strong>CHA:</strong> {{ $instrutorEscolaMta->cha_numero ?? '—' }}</p>
        </div>
    @endif

    @foreach ($aula->alunos as $aluno)
        <div class="card">
            <h2>Aluno: {{ $aluno->nome }}</h2>
            <p><strong>CPF:</strong> {{ $aluno->cpf }}</p>
            <p><strong>Aula:</strong> {{ $aula->numero_oficio }} — {{ optional($aula->data_aula)->format('d/m/Y') }} — {{ $aula->local }}</p>
            <p class="muted">Conteúdo do atestado MTA será substituído pelo modelo oficial.</p>
        </div>
    @endforeach
</body>
</html>

