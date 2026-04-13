@props([
    'processo',
])

@php
    use App\Enums\ProcessoStatus;
    use App\Enums\TipoProcessoCategoria;

    $tipo = $processo->tipoProcesso ?? $processo->tipoProcessoTenant;
    $cat = $tipo?->categoria;
    if ($cat !== null && ! $cat instanceof TipoProcessoCategoria && is_string($cat)) {
        $cat = TipoProcessoCategoria::tryFrom($cat);
    }
    $st = $processo->status;
    $circle = $st instanceof ProcessoStatus
        ? $st->uiListStatusAvatarSolidClass()
        : 'bg-slate-600 shadow-md shadow-slate-600/25';
    $fg = $st instanceof ProcessoStatus
        ? $st->uiListStatusAvatarForegroundClass()
        : 'text-white';
    $label = $tipo?->nome ?? __('Processo');
@endphp

<div {{ $attributes->class(['flex h-12 w-12 shrink-0 items-center justify-center rounded-full '.$fg.' '.$circle]) }}>
    @if ($cat === TipoProcessoCategoria::Cha)
        @include('habilitacoes.partials.icon-cha-menu', ['svgClass' => 'h-7 w-7'])
    @elseif ($cat === TipoProcessoCategoria::Embarcacao)
        @include('embarcacoes.partials.icon-embarcacoes-nav', ['svgClass' => 'h-7 w-7'])
    @else
        {{-- CIR ou tipo sem categoria: mesmo ícone do item «Processos» no menu --}}
        <svg class="h-7 w-7 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
        </svg>
    @endif
    <span class="sr-only">{{ $label }}</span>
</div>
