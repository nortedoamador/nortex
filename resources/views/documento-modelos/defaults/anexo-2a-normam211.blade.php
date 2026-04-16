@include('documento-modelos.partials.normam211-212-vars')

@php
    $titulo = 'ANEXO 2-A — BADE/BSADE (NORMAM-211)';
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
<p class="muted">
    Modelo básico gerado automaticamente pelo sistema (pré-preenchido). Ajuste manualmente se necessário.
</p>

<table>
    <tr>
        <th colspan="2">Interessado</th>
    </tr>
    <tr>
        <td><strong>Nome</strong><br>{{ $nome ?? '' }}</td>
        <td><strong>CPF</strong><br>{{ $cpf ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Endereço</strong><br>{{ $endereco_completo ?? ($endereco ?? '') }}</td>
    </tr>
</table>

<br>

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
</table>

<br>

<p>
    <strong>Local e data</strong>: {{ $local_declaracao ?? ($cidade ?? '') }} — {{ $data ?? '' }}
</p>

<p style="margin-top: 28px;">
    _______________________________________________<br>
    <strong>Assinatura do interessado</strong>
</p>

@include('documento-modelos.partials.nx-pdf24-impressao-a4')

