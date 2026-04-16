@props([
    'processo',
    'chromeWrapClass',
    /** Na lista de processos: largura alinhada ao rótulo mais longo entre as opções. Na ficha: largura total do contentor. */
    'fitLongestLabel' => true,
])

@php
    /** @var \App\Models\Processo $processo */
    $nxOpts = $processo->statusesPermitidosParaAlteracao();
    $nxLabels = collect($nxOpts)->mapWithKeys(fn (\App\Enums\ProcessoStatus $s) => [$s->value => $s->label()])->all();
    $nxSelected = $processo->status;
    $nxLongestLabel = collect($nxLabels)
        ->sortByDesc(fn ($l) => mb_strlen((string) $l))
        ->first() ?? '';
@endphp

<div
    {{ $attributes->class([
        'nx-processo-status-cs-root min-w-0 '.$chromeWrapClass,
        $fitLongestLabel ? 'w-max max-w-full' : 'w-full',
    ]) }}
    x-data="nxProcessoStatusCustomSelect({ initialValue: @js($nxSelected->value), labels: @js($nxLabels) })"
    x-init="init()"
    @keydown.escape.window="open && (open = false)"
    @scroll.window="open && (open = false)"
    @resize.window="open && (open = false)"
    @click.outside="open = false"
>
    <select
        x-ref="nativeSelect"
        name="status"
        data-nx-processo-list-status="1"
        @change="syncFromNative()"
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
    >
        @foreach ($nxOpts as $opt)
            <option value="{{ $opt->value }}" @selected($nxSelected === $opt)>{{ $opt->label() }}</option>
        @endforeach
    </select>

    @if ($fitLongestLabel)
        <div class="relative grid w-full min-w-0 max-w-full justify-items-stretch">
            <div
                class="col-start-1 row-start-1 flex min-w-0 items-center gap-2 rounded-r-[10px] py-2.5 pl-3 pr-10 invisible pointer-events-none"
                aria-hidden="true"
            >
                <span class="h-5 w-5 shrink-0"></span>
                <span class="whitespace-nowrap text-sm font-medium">{{ $nxLongestLabel }}</span>
            </div>
            <button
                type="button"
                x-ref="triggerBtn"
                class="col-start-1 row-start-1 relative flex w-full min-w-0 items-center gap-2 rounded-r-[10px] py-2.5 pl-3 pr-10 text-left transition hover:bg-slate-50/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/30 focus-visible:ring-inset dark:hover:bg-slate-800/60"
                @click="toggle()"
                :aria-expanded="open"
                aria-haspopup="listbox"
                aria-label="{{ __('Alterar etapa do processo') }}"
            >
                @foreach ($nxOpts as $opt)
                    <template x-if="value === @js($opt->value)">
                        <span class="shrink-0 {{ $opt->uiStatusSelectIconClass() }}">
                            @include('processos.partials.status-filter-icon', ['status' => $opt, 'class' => 'h-5 w-5 shrink-0'])
                        </span>
                    </template>
                @endforeach
                <span class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 dark:text-slate-100" x-text="label"></span>
                <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true">
                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </span>
            </button>
        </div>
    @else
        <button
            type="button"
            x-ref="triggerBtn"
            class="relative flex w-full min-w-0 items-center gap-2 rounded-r-[10px] py-2.5 pl-3 pr-10 text-left transition hover:bg-slate-50/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/30 focus-visible:ring-inset dark:hover:bg-slate-800/60"
            @click="toggle()"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-label="{{ __('Alterar etapa do processo') }}"
        >
            @foreach ($nxOpts as $opt)
                <template x-if="value === @js($opt->value)">
                    <span class="shrink-0 {{ $opt->uiStatusSelectIconClass() }}">
                        @include('processos.partials.status-filter-icon', ['status' => $opt, 'class' => 'h-5 w-5 shrink-0'])
                    </span>
                </template>
            @endforeach
            <span class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 dark:text-slate-100" x-text="label"></span>
            <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true">
                <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </span>
        </button>
    @endif

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed z-[200] max-h-72 overflow-y-auto rounded-xl border border-slate-200/90 bg-white py-1 shadow-xl ring-1 ring-slate-900/5 dark:border-slate-600 dark:bg-slate-900 dark:ring-white/10"
        :style="panelStyle"
        role="listbox"
    >
        @foreach ($nxOpts as $opt)
            <button
                type="button"
                role="option"
                :aria-selected="value === @js($opt->value)"
                class="flex w-full items-center gap-3 px-3 py-2.5 text-left transition"
                :class="value === @js($opt->value)
                    ? 'bg-slate-100 dark:bg-slate-800'
                    : 'hover:bg-slate-50 dark:hover:bg-slate-800/70'"
                @click="choose(@js($opt->value))"
            >
                <span class="shrink-0 {{ $opt->uiStatusSelectIconClass() }}">
                    @include('processos.partials.status-filter-icon', ['status' => $opt, 'class' => 'h-5 w-5 shrink-0'])
                </span>
                <span class="min-w-0 flex-1 text-sm font-medium text-slate-800 dark:text-slate-100">{{ $opt->label() }}</span>
            </button>
        @endforeach
    </div>
</div>
