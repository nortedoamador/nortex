<x-app-layout title="{{ __('Ficha da habilitação') }}">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Ficha da habilitação') }}</h2>
            <a href="{{ route('habilitacoes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-500 dark:text-slate-400">{{ __('← Habilitações') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8" x-data="{ previewId: null }">
        <div class="mx-auto grid max-w-[1600px] gap-6 lg:grid-cols-[320px_1fr]">
            <aside class="space-y-4">
                @php
                    $cpfFmt = $habilitacao->cpfFormatadoTitular() ?? $habilitacao->cpf ?? '—';
                    $nomeUpper = \Illuminate\Support\Str::upper((string) ($habilitacao->nome ?? ''));
                    $vencida = $habilitacao->data_validade && $habilitacao->data_validade->isPast();
                @endphp

                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 7a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3l0 -10" />
                                <path d="M7 10a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M15 8l2 0" />
                                <path d="M15 12l2 0" />
                                <path d="M7 16l10 0" />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-extrabold tracking-wide text-slate-900 dark:text-white">
                            {{ $nomeUpper ?: '—' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $habilitacao->categoria }}</p>
                        <p class="mt-1 text-xs font-mono text-slate-500 dark:text-slate-400">{{ $cpfFmt }}</p>

                        <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide
                            {{ $vencida ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' }}">
                            <span class="h-2 w-2 rounded-full {{ $vencida ? 'bg-amber-400' : 'bg-emerald-500' }}"></span>
                            {{ $vencida ? __('Vencida') : __('Em vigor') }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-2 text-sm">
                        <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                            <div class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                {{ __('Anexos') }}
                            </div>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                                {{ $habilitacao->anexos->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @if ($habilitacao->cliente)
                            <a href="{{ route('clientes.show', $habilitacao->cliente) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:focus:ring-emerald-400/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                {{ __('Ver cliente') }}
                            </a>
                        @endif
                        @can('update', $habilitacao)
                            <a href="{{ route('habilitacoes.edit', $habilitacao) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                {{ __('Alterar cadastro') }}
                            </a>
                        @endcan
                    </div>
                </div>


                <nav class="rounded-2xl border border-slate-200/80 bg-white p-3 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <a href="#dados" class="flex items-center gap-2 rounded-xl px-3 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/60">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span> {{ __('Dados') }}
                    </a>
                    <a href="#anexos" class="flex items-center gap-2 rounded-xl px-3 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/60">
                        <span class="h-2 w-2 rounded-full bg-slate-300 dark:bg-slate-600"></span> {{ __('Anexos') }}
                    </a>
                    @can('manage', $habilitacao)
                        <a href="#envio" class="flex items-center gap-2 rounded-xl px-3 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/60">
                            <span class="h-2 w-2 rounded-full bg-slate-300 dark:bg-slate-600"></span> {{ __('Documentos para envio') }}
                        </a>
                    @endcan
                </nav>
            </aside>

            <div class="space-y-6">
                @if (session('status'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                <section id="dados" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                            <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 8V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v4" /></svg>
                            {{ __('Dados da CHA') }}
                        </span>
                        @can('update', $habilitacao)
                            <a href="{{ route('habilitacoes.edit', $habilitacao) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('Editar') }}</a>
                        @endcan
                    </div>
                    <div class="grid divide-y divide-slate-200 text-sm dark:divide-slate-800 sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
                        <div class="p-6">
                            <dl class="divide-y divide-slate-200 dark:divide-slate-800">
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Cliente') }}</dt>
                                    <dd class="max-w-[55%] text-right font-medium text-slate-900 dark:text-slate-100">
                                        @if ($habilitacao->cliente)
                                            <a href="{{ route('clientes.show', $habilitacao->cliente) }}" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ $habilitacao->cliente->nome }}</a>
                                        @else
                                            —
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('CPF') }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $cpfFmt }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Nascimento') }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $habilitacao->data_nascimento?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Categoria') }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $habilitacao->categoria ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Número CHA') }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $habilitacao->numero_cha ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="p-6">
                            <dl class="divide-y divide-slate-200 dark:divide-slate-800">
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Emissão') }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $habilitacao->data_emissao?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Vencimento') }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $habilitacao->data_validade?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                                <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Jurisdição') }}</dt>
                                    <dd class="max-w-[min(100%,220px)] text-right font-medium text-slate-900 dark:text-slate-100 sm:max-w-[55%]">{{ $habilitacao->jurisdicao ?? '—' }}</dd>
                                </div>
                            </dl>
                            @if ($habilitacao->observacoes)
                                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-xs text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300">
                                    <p class="font-semibold text-slate-600 dark:text-slate-400">{{ __('Observações') }}</p>
                                    <p class="mt-1 whitespace-pre-wrap">{{ $habilitacao->observacoes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section id="anexos" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2 border-b border-slate-200 px-6 py-4 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-white">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.381-7.69 7.69" /></svg>
                        {{ __('Anexos') }}
                    </div>
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($habilitacao->anexos as $anexo)
                            <li class="space-y-2 px-6 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="max-w-[200px] truncate text-sm font-medium text-slate-900 dark:text-slate-100 sm:max-w-md">{{ $anexo->nome_original }}</span>
                                        @if (filled($anexo->tipo_codigo))
                                            <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ \App\Support\HabilitacaoAnexoTiposCha::label($anexo->tipo_codigo) }}
                                            </span>
                                        @endif
                                        @php $vs = $anexo->extra_validation_status; @endphp
                                        <span class="rounded-full px-2 py-0.5 text-[10px] uppercase
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
                                            :nova-aba-url="$anexo->signedInlineUrl()"
                                            :download-url="$anexo->signedDownloadUrl()"
                                            :print-url="$anexo->signedPrintUrl()"
                                            :destroy-url="Auth::user()->can('manage', $habilitacao) ? $anexo->opaqueDestroyUrl() : null"
                                        />
                                    </div>
                                </div>
                                @if ($anexo->extra_validation_notes)
                                    <p class="text-xs text-slate-600 dark:text-slate-400">{{ $anexo->extra_validation_notes }}</p>
                                @endif
                                <div x-show="previewId === {{ $anexo->id }}" class="pt-2" x-cloak>
                                    <x-anexo-preview :url="$anexo->signedInlineUrl()" :mime="$anexo->mime" :nome="$anexo->nome_original" />
                                </div>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum anexo.') }}</li>
                        @endforelse
                    </ul>
                </section>

                @can('manage', $habilitacao)
                    <section id="envio" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                            <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            {{ __('Documentos para envio') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('O envio de arquivos é opcional. Indique o tipo do documento e envie um ou mais arquivos.') }}</p>
                        <x-input-error :messages="$errors->get('arquivos')" class="mt-3" />
                        <x-input-error :messages="$errors->get('tipo_codigo')" class="mt-2" />
                        <form
                            method="POST"
                            action="{{ route('habilitacoes.anexos.store', $habilitacao) }}"
                            enctype="multipart/form-data"
                            class="mt-4 space-y-3"
                            x-data="{
                                drag: false,
                                files: [],
                                tipoCodigo: @js(old('tipo_codigo', '')),
                                tipoTexto() {
                                    const el = this.$refs.tipoChaSelect;
                                    if (! el || ! el.value) return '—';
                                    const opt = el.options[el.selectedIndex];
                                    return (opt && opt.text) ? opt.text.trim() : '—';
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
                            <div>
                                <label for="habilitacao-anexo-tipo-cha" class="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-500 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                    {{ __('Tipo de anexo da CHA') }}
                                </label>
                                <select
                                    id="habilitacao-anexo-tipo-cha"
                                    x-ref="tipoChaSelect"
                                    x-model="tipoCodigo"
                                    name="tipo_codigo"
                                    required
                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                >
                                    <option value="" @selected(! filled(old('tipo_codigo')))>{{ __('Selecione…') }}</option>
                                    @foreach (\App\Support\HabilitacaoAnexoTiposCha::opcoes() as $valor => $rotulo)
                                        <option value="{{ $valor }}" @selected(old('tipo_codigo') === $valor)>{{ $rotulo }}</option>
                                    @endforeach
                                </select>
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
                                        <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="tipoTexto()"></span>
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
