@php
    $fa = $filtrosAvancados ?? [];
@endphp
@if (($fa['tipo'] ?? 0) > 0)
    <input type="hidden" name="redirect_tipo" value="{{ $fa['tipo'] }}" />
@endif
@if (filled($fa['cat'] ?? null))
    <input type="hidden" name="redirect_cat" value="{{ $fa['cat'] }}" />
@endif
@if (filled($fa['jurisdicao'] ?? null))
    <input type="hidden" name="redirect_jurisdicao" value="{{ $fa['jurisdicao'] }}" />
@endif
@if (($fa['cliente'] ?? 0) > 0)
    <input type="hidden" name="redirect_cliente" value="{{ $fa['cliente'] }}" />
@endif
@if (($fa['processo'] ?? 0) > 0)
    <input type="hidden" name="redirect_processo" value="{{ $fa['processo'] }}" />
@endif
@if (! empty($fa['doc_pendente']))
    <input type="hidden" name="redirect_doc_pendente" value="1" />
@endif
@if (filled($fa['atualizado_de'] ?? null))
    <input type="hidden" name="redirect_atualizado_de" value="{{ $fa['atualizado_de'] }}" />
@endif
@if (filled($fa['atualizado_ate'] ?? null))
    <input type="hidden" name="redirect_atualizado_ate" value="{{ $fa['atualizado_ate'] }}" />
@endif
