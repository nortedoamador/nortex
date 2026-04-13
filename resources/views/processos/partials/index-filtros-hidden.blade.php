@php
    $fa = $filtrosAvancados ?? [];
@endphp
@if (($fa['tipo'] ?? 0) > 0)
    <input type="hidden" name="tipo" value="{{ $fa['tipo'] }}" />
@endif
@if (filled($fa['cat'] ?? null))
    <input type="hidden" name="cat" value="{{ $fa['cat'] }}" />
@endif
@if (filled($fa['jurisdicao'] ?? null))
    <input type="hidden" name="jurisdicao" value="{{ $fa['jurisdicao'] }}" />
@endif
@if (($fa['cliente'] ?? 0) > 0)
    <input type="hidden" name="cliente" value="{{ $fa['cliente'] }}" />
@endif
@if (($fa['processo'] ?? 0) > 0)
    <input type="hidden" name="processo" value="{{ $fa['processo'] }}" />
@endif
@if (! empty($fa['doc_pendente']))
    <input type="hidden" name="doc_pendente" value="1" />
@endif
@if (filled($fa['atualizado_de'] ?? null))
    <input type="hidden" name="atualizado_de" value="{{ $fa['atualizado_de'] }}" />
@endif
@if (filled($fa['atualizado_ate'] ?? null))
    <input type="hidden" name="atualizado_ate" value="{{ $fa['atualizado_ate'] }}" />
@endif
