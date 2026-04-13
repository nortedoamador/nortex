<x-app-layout title="{{ __('Ficha do cliente') }}">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Ficha do Cliente') }}</h2>
            <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-500 dark:text-slate-400">{{ __('← Clientes') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8" x-data="{ previewId: null }">
        <div class="mx-auto grid max-w-[1600px] gap-6 lg:grid-cols-[320px_1fr]">
            {{-- Painel lateral --}}
            <aside class="space-y-4">
                @php
                    $cidadeUf = trim(($cliente->cidade ?? '').(($cliente->cidade && $cliente->uf) ? '/' : '').($cliente->uf ?? ''));
                    $celularDigits = preg_replace('/\D/', '', (string) ($cliente->celular ?? ''));
                    $whatsUrl = $celularDigits ? 'https://wa.me/55'.$celularDigits : null;
                    $enderecoLinha = trim(($cliente->endereco ?? '').(($cliente->numero) ? ', '.$cliente->numero : ''));
                    $mapsQuery = trim($enderecoLinha.' '.($cliente->cidade ?? '').' '.($cliente->uf ?? ''));
                    $mapsUrl = $mapsQuery ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($mapsQuery) : null;
                @endphp

                {{-- Card: Perfil --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-3xl font-bold text-white shadow-md">
                            {{ $cliente->iniciaisAvatar() }}
                        </div>
                        <p class="mt-4 text-sm font-extrabold tracking-wide text-slate-900 dark:text-white">
                            {{ \Illuminate\Support\Str::upper($cliente->nome) }}
                        </p>
                        <p class="mt-1 inline-flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            {{ $cidadeUf ?: '—' }}
                        </p>

                        <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span> {{ __('Ativo') }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-2 text-sm">
                        <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                            <div class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                {{ __('Processos') }}
                            </div>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                                {{ $cliente->processos->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                            <div class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18.75c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V9.75L12 3 3 9.75v9Z" /></svg>
                                {{ __('Embarcações') }}
                            </div>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                                {{ $cliente->embarcacoes->count() }}
                            </span>
                        </div>
                        @if (Auth::user()->hasPermission('habilitacoes.view'))
                            <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                                <div class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 8V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v4" />
                                    </svg>
                                    {{ __('Habilitações') }}
                                </div>
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                                    {{ $cliente->habilitacoes->count() }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 space-y-3">
                        <a
                            href="{{ $whatsUrl ?: '#contato' }}"
                            @class([
                                'inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-extrabold shadow-sm focus:outline-none focus:ring-2',
                                'bg-emerald-600 text-white hover:bg-emerald-500 focus:ring-emerald-500/30 dark:focus:ring-emerald-400/30' => (bool) $whatsUrl,
                                'cursor-not-allowed bg-emerald-200 text-emerald-900/60 opacity-70 dark:bg-emerald-950/40 dark:text-emerald-200/60' => ! $whatsUrl,
                            ])
                            @if($whatsUrl) target="_blank" rel="noopener" @endif
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-4.5 8.25 2.347-2.347A4.5 4.5 0 0 1 12.53 16.5H18a2.25 2.25 0 0 0 2.25-2.25v-6A2.25 2.25 0 0 0 18 6H6A2.25 2.25 0 0 0 3.75 8.25v6A2.25 2.25 0 0 0 6 16.5h1.5Z" /></svg>
                            {{ __('Enviar Mensagem') }}
                        </a>

                        @can('update', $cliente)
                            <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex w-full items-center justify-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                {{ __('Alterar Cadastro') }}
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- Card: Dados de contato --}}
                <section id="contato" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="bg-gradient-to-r from-indigo-500 to-violet-600 px-5 py-3 text-xs font-extrabold uppercase tracking-wide text-white">
                        <div class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0-.621.504-1.125 1.125-1.125h3.078c.558 0 1.05.393 1.17.935l.96 4.322a1.125 1.125 0 0 1-.636 1.271l-2.034.96a11.946 11.946 0 0 0 5.07 5.07l.96-2.034a1.125 1.125 0 0 1 1.271-.636l4.322.96c.542.12.935.612.935 1.17v3.078c0 .621-.504 1.125-1.125 1.125H18C9.716 21.75 3 15.034 3 6.75V6.75Z" /></svg>
                            {{ __('Dados de contato') }}
                        </div>
                    </div>

                    <div class="space-y-4 p-5 text-sm">
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0-.621.504-1.125 1.125-1.125h3.078c.558 0 1.05.393 1.17.935l.96 4.322a1.125 1.125 0 0 1-.636 1.271l-2.034.96a11.946 11.946 0 0 0 5.07 5.07l.96-2.034a1.125 1.125 0 0 1 1.271-.636l4.322.96c.542.12.935.612.935 1.17v3.078c0 .621-.504 1.125-1.125 1.125H18C9.716 21.75 3 15.034 3 6.75V6.75Z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('Celular') }}</p>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $cliente->celularFormatado() ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15A2.25 2.25 0 0 1 2.25 17.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('E-mail') }}</p>
                                @if (filled($cliente->email))
                                    <a href="mailto:{{ $cliente->email }}" class="break-all font-medium text-indigo-600 hover:underline dark:text-indigo-300">{{ $cliente->email }}</a>
                                @else
                                    <p class="font-medium text-slate-900 dark:text-white">—</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('Localização') }}</p>
                                <p class="font-medium text-slate-900 dark:text-white">
                                    {{ $enderecoLinha ?: '—' }}
                                </p>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $cidadeUf ?: '—' }}</p>
                                    @if ($mapsUrl)
                                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline dark:text-indigo-300" title="{{ __('Abrir no mapa') }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 0L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>

            {{-- Conteúdo --}}
            <div class="space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <section id="ficha" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2 border-b border-slate-200 px-6 py-4 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-white">
                    <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5ZM8.25 9h.008v.008H8.25V9Zm0 3h.008v.008H8.25V12Zm0 3h.008v.008H8.25V15Z" /></svg>
                    {{ __('Ficha de cadastro') }}
                </div>
                <div class="grid divide-y divide-slate-200 text-sm dark:divide-slate-800 sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
                    <div class="p-6">
                        <dl class="divide-y divide-slate-200 dark:divide-slate-800">
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('CPF/CNPJ') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->documentoFormatado() ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Documento (RG/CNH)') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->documento_identidade_numero ?? $cliente->rg ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Órgão emissor') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->orgao_emissor ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Data de emissão') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->data_emissao_rg?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Nacionalidade') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->nacionalidade ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Naturalidade') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->naturalidade ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="p-6">
                        <dl class="divide-y divide-slate-200 dark:divide-slate-800">
                            <div class="py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Endereço') }}</dt>
                                <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">
                                    {{ $cliente->endereco ?? '—' }}@if ($cliente->numero), {{ __('nº') }} {{ $cliente->numero }}@endif
                                    @if ($cliente->complemento) — {{ $cliente->complemento }} @endif
                                    @if ($cliente->apartamento) — {{ __('Apto.') }} {{ $cliente->apartamento }} @endif
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Bairro') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->bairro ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('CEP') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->cepFormatado() ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Cidade/UF') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ trim(($cliente->cidade ?? '').($cliente->cidade && $cliente->uf ? ' / ' : '').($cliente->uf ?? '')) ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Telefone') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->telefoneFormatado() ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Celular') }}</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cliente->celularFormatado() ?? '—' }}</dd>
                            </div>
                            <div class="py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('E-mail') }}</dt>
                                <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $cliente->email ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            @if ($cliente->processos->isNotEmpty())
                <section id="processos" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        {{ __('Processos vinculados') }}
                    </h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($cliente->processos as $proc)
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="min-w-0">
                                    <a href="{{ route('processos.show', $proc) }}" class="truncate font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        {{ $proc->tipoProcesso?->nome ?? __('Processo #:id', ['id' => $proc->id]) }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $proc->tipoProcesso->nome ?? '—' }}</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                                    <x-processo-docs-pendente-badge :processo="$proc" />
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                        {{ $proc->status->label() }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($cliente->embarcacoes->isNotEmpty())
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18.75c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V9.75L12 3 3 9.75v9Z" /></svg>
                        {{ __('Embarcações vinculadas') }}
                    </h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($cliente->embarcacoes as $emb)
                            @php
                                $embMeta = collect([$emb->tipo, $emb->cor_casco])->filter(fn ($v) => filled($v))->implode(' · ');
                            @endphp
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="min-w-0">
                                    <a href="{{ route('embarcacoes.show', $emb) }}" class="block truncate font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        {{ $emb->nome ?: '—' }}
                                    </a>
                                    @if ($embMeta !== '')
                                        <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $embMeta }}</p>
                                    @endif
                                </div>
                                @if (filled($emb->inscricao))
                                    <span class="shrink-0 rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                        {{ $emb->inscricao }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (Auth::user()->hasPermission('habilitacoes.view') && $cliente->habilitacoes->isNotEmpty())
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 8V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v4" /></svg>
                        {{ __('Habilitação (CHA) vinculada') }}
                    </h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($cliente->habilitacoes as $h)
                            @php $hVencida = $h->data_validade && $h->data_validade->isPast(); @endphp
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="min-w-0">
                                    <a href="{{ route('habilitacoes.show', $h) }}" class="block truncate font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        {{ $h->categoria }} — {{ $h->numero_cha ?? '—' }}
                                    </a>
                                    @if ($h->data_validade)
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Válido até :data', ['data' => $h->data_validade->format('d/m/Y')]) }}</p>
                                    @endif
                                </div>
                                @if ($h->data_validade)
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $hVencida ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200' }}">
                                        {{ $hVencida ? __('Vencida') : __('Em vigor') }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section id="anexos" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2 border-b border-slate-200 px-6 py-4 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-white">
                    <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.381-7.69 7.69" /></svg>
                    {{ __('Anexos do cliente') }}
                </div>
                <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($cliente->anexos as $anexo)
                        <li class="space-y-2 px-6 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="max-w-[200px] truncate text-sm font-medium text-slate-900 dark:text-slate-100 sm:max-w-md">{{ $anexo->nome_original }}</span>
                                    @if (filled($anexo->tipo_codigo))
                                        <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $anexo->tipoLabel() }}</span>
                                    @endif
                                    @php $vs = $anexo->extra_validation_status; @endphp
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                        @if($vs->value === 'ok') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200
                                        @elseif($vs->value === 'pendente') bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200
                                        @elseif($vs->value === 'falhou') bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-200
                                        @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 @endif">
                                        {{ $vs->label() }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <button type="button" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400" @click="previewId = previewId === {{ $anexo->id }} ? null : {{ $anexo->id }}">
                                        <span x-text="previewId === {{ $anexo->id }} ? @js(__('Ocultar preview')) : @js(__('Preview'))"></span>
                                    </button>
                                    <x-anexo-list-icon-actions
                                        :nova-aba-url="route('clientes.anexos.inline', [$cliente, $anexo])"
                                        :download-url="route('clientes.anexos.download', [$cliente, $anexo])"
                                        :print-url="route('clientes.anexos.print', [$cliente, $anexo])"
                                        :destroy-url="Auth::user()->can('manage', $cliente) ? route('clientes.anexos.destroy', [$cliente, $anexo]) : null"
                                    />
                                </div>
                            </div>
                            @if ($anexo->extra_validation_notes)
                                <p class="text-xs text-slate-600 dark:text-slate-400">{{ $anexo->extra_validation_notes }}</p>
                            @endif
                            <div x-show="previewId === {{ $anexo->id }}" class="pt-2" x-cloak>
                                <x-anexo-preview :url="route('clientes.anexos.inline', [$cliente, $anexo])" :mime="$anexo->mime" :nome="$anexo->nome_original" />
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Nenhum anexo encontrado') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Envie documentos na seção abaixo (se permitido).') }}</p>
                        </li>
                    @endforelse
                </ul>
            </section>

            @can('manage', $cliente)
                <section id="envio" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        {{ __('Documentos para envio') }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('O envio de arquivos é opcional. Indique o tipo do documento, se quiser (ex.: RG, passaporte), e envie um ou mais arquivos.') }}</p>
                    <x-input-error :messages="$errors->get('arquivos')" class="mt-3" />
                    @php
                        $tipoEnvioOld = (string) old('tipo_codigo', '');
                        $tipoEnvioPreset = match ($tipoEnvioOld) {
                            \App\Support\ClienteTiposAnexo::CNH => \App\Support\ClienteTiposAnexo::CNH,
                            \App\Support\ClienteTiposAnexo::COMPROVANTE_ENDERECO => \App\Support\ClienteTiposAnexo::COMPROVANTE_ENDERECO,
                            'RG' => 'RG',
                            default => ($tipoEnvioOld !== '' ? '__outro' : ''),
                        };
                        $tipoEnvioCustom = $tipoEnvioPreset === '__outro' ? $tipoEnvioOld : '';
                    @endphp
                    <form
                        method="POST"
                        action="{{ route('clientes.anexos.store', $cliente) }}"
                        enctype="multipart/form-data"
                        class="mt-4 space-y-3"
                        x-data="{
                            drag: false,
                            files: [],
                            preset: @js($tipoEnvioPreset),
                            custom: @js($tipoEnvioCustom),
                            tipoTexto() {
                                if (this.preset === '__outro') return (this.custom || '').trim();
                                return (this.preset || '').trim();
                            },
                            updateFiles() {
                                const input = this.$refs.fileInputOutro;
                                this.files = input?.files ? Array.from(input.files) : [];
                            },
                            setFilesFromDrop(e) {
                                const input = this.$refs.fileInputOutro;
                                const dt = new DataTransfer();
                                for (const f of e.dataTransfer.files) dt.items.add(f);
                                input.files = dt.files;
                                this.updateFiles();
                            },
                            removeAt(i) {
                                const input = this.$refs.fileInputOutro;
                                const dt = new DataTransfer();
                                this.files.forEach((f, idx) => { if (idx !== i) dt.items.add(f); });
                                input.files = dt.files;
                                this.updateFiles();
                            },
                        }"
                        x-init="updateFiles()"
                    >
                        @csrf
                        <div class="grid gap-3">
                            <div>
                                <label class="mb-1 flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-500 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                    {{ __('Tipo (opcional)') }}
                                </label>
                                <select
                                    name="tipo_codigo_preset"
                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                    x-model="preset"
                                >
                                    <option value="" @selected($tipoEnvioPreset === '')>{{ __('Selecione…') }}</option>
                                    <option value="RG">{{ __('RG') }}</option>
                                    <option value="{{ \App\Support\ClienteTiposAnexo::CNH }}">{{ __('CNH') }}</option>
                                    <option value="{{ \App\Support\ClienteTiposAnexo::COMPROVANTE_ENDERECO }}">{{ __('Comprovante de endereço') }}</option>
                                    <option value="__outro">{{ __('Outro anexo (digite)') }}</option>
                                </select>
                            </div>
                            <div x-show="preset === '__outro'" x-cloak>
                                <label class="mb-1 flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-500 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    {{ __('Tipo do outro anexo') }}
                                </label>
                                <input
                                    type="text"
                                    name="tipo_codigo_custom"
                                    x-model="custom"
                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                    maxlength="64"
                                    placeholder="{{ __('Ex.: Passaporte') }}"
                                />
                            </div>
                        </div>
                        <input
                            type="file"
                            name="arquivos[]"
                            multiple
                            class="hidden"
                            x-ref="fileInputOutro"
                            accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
                            @change="updateFiles()"
                        />
                        <div
                            @dragover.prevent="drag = true"
                            @dragleave.prevent="drag = false"
                            @drop.prevent="drag = false; setFilesFromDrop($event)"
                            :class="drag ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : 'border-slate-200 dark:border-slate-700'"
                            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-3 py-6 text-center text-xs text-slate-600 dark:text-slate-400"
                            @click="$refs.fileInputOutro.click()"
                        >
                            <svg class="h-8 w-8 shrink-0 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.28 4.5 4.5 0 0 1 4.34 4.476 4.5 4.5 0 0 1-6.335 4.46" /></svg>
                            <span class="block font-semibold text-slate-700 dark:text-slate-200">{{ __('Arraste e solte aqui') }}</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ __('ou selecione arquivos no seu dispositivo') }}</span>
                        </div>

                        <div x-show="files.length > 0" class="rounded-xl border border-slate-200/80 bg-white/60 p-3 text-xs dark:border-slate-700/80 dark:bg-slate-900/40" x-cloak>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <p class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
                                    <svg class="h-4 w-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                                    {{ __('Arquivos selecionados') }}
                                </p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="tipoTexto() || '—'"></span>
                                    <span class="text-slate-400 dark:text-slate-500">·</span>
                                    <span x-text="files.length"></span>
                                </p>
                            </div>
                            <ul class="space-y-2">
                                <template x-for="(f, i) in files" :key="f.name + '_' + f.size + '_' + i">
                                    <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-slate-700/80 dark:bg-slate-900">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-800 dark:text-slate-100" x-text="f.name"></p>
                                            <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                                <span x-text="(f.type || '').toUpperCase() || '—'"></span>
                                                <span class="text-slate-400 dark:text-slate-500">·</span>
                                                <span x-text="Math.max(1, Math.round((f.size || 0) / 1024)) + ' KB'"></span>
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                            @click="removeAt(i)"
                                        >{{ __('Remover') }}</button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <x-primary-button type="submit" class="w-full justify-center !py-2.5 text-xs">{{ __('Enviar') }}</x-primary-button>
                    </form>
                </section>
            @endcan

            </div>
        </div>
    </div>
</x-app-layout>
