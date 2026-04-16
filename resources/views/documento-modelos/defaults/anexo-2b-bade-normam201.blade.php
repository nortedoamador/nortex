@include('documento-modelos.partials.normam211-212-vars')

@php
    $titulo = 'ANEXO 2-B — BADE (NORMAM-201)';
@endphp

<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
    h1 { font-size: 14px; margin: 0 0 10px; }
    .muted { color: #475569; }
    table { width: 100%; border-collapse: collapse; }
    td, th { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }
    th { background: #f1f5f9; text-align: left; }
</style>

<h1>{{ $titulo }}</h1>
<p class="muted">Modelo básico pré-preenchido pelo sistema.</p>

<table>
    <tr>
        <th colspan="2">Embarcação</th>
    </tr>
    <tr>
        <td><strong>Nome</strong><br>{{ $nome_embarcacao ?? '' }}</td>
        <td><strong>Inscrição</strong><br>{{ $inscricao ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Tipo</strong><br>{{ $tipo ?? '' }}</td>
        <td><strong>Comprimento</strong><br>{{ $comprimento ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Construtor</strong><br>{{ $construtor ?? '' }}</td>
        <td><strong>Ano</strong><br>{{ $ano ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Motor (marca)</strong><br>{{ $marca_motor ?? '' }}</td>
        <td><strong>Motor (nº)</strong><br>{{ $numero_motor ?? '' }}</td>
    </tr>
</table>

<p style="margin-top: 18px;">
    <strong>Local e data</strong>: {{ $local_declaracao ?? ($cidade ?? '') }} — {{ $data ?? '' }}
</p>

<p style="margin-top: 28px;">
    _______________________________________________<br>
    <strong>Assinatura do interessado</strong>
</p>

@include('documento-modelos.partials.nx-pdf24-impressao-a4')

