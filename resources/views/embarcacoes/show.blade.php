<x-app-layout title="{{ __('Ficha da embarcação') }}">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Ficha da Embarcação') }}</h2>
            <div class="flex flex-wrap items-center gap-3">
                @can('update', $embarcacao)
                    <a href="{{ route('embarcacoes.edit', $embarcacao) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                        {{ __('Editar') }}
                    </a>
                @endcan
                <a href="{{ route('embarcacoes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-500 dark:text-slate-400">{{ __('← Embarcações') }}</a>
            </div>
        </div>
    </x-slot>

    <div
        class="px-4 py-6 sm:px-6 lg:px-8"
        x-data="{
            previewId: null,
            fotosUploadIrEAbrir() {
                const el = document.getElementById('fotos-upload');
                if (! el) {
                    return;
                }
                const input = el.querySelector('input[type=file]');
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', '#fotos-upload');
                }
                if (input) {
                    input.click();
                }
            },
            arquivosUploadIrEAbrir() {
                const el = document.getElementById('envio');
                if (! el) {
                    return;
                }
                const input = el.querySelector('input[type=file]');
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', '#envio');
                }
                if (input) {
                    input.click();
                }
            }
        }"
    >
        <div class="mx-auto grid max-w-[1600px] gap-6 lg:grid-cols-[320px_1fr] lg:items-start">
            {{-- Coluna principal define a altura da linha; aside com self-stretch ganha a mesma altura para o sticky ter “folga” dentro do pai (senão aside === filho e sticky não gruda). --}}
            <aside class="min-w-0 lg:self-stretch">
                <div class="space-y-4 lg:sticky lg:top-6 lg:z-10">
                @php
                    $cpfDigits = preg_replace('/\D/', '', (string) ($embarcacao->cpf ?? ''));
                    $cpfFmt = $embarcacao->cpf ?? '—';
                    if (strlen($cpfDigits) === 11) {
                        $cpfFmt = substr($cpfDigits, 0, 3).'.'.substr($cpfDigits, 3, 3).'.'.substr($cpfDigits, 6, 3).'-'.substr($cpfDigits, 9, 2);
                    }
                    $nomeUpper = \Illuminate\Support\Str::upper((string) ($embarcacao->nome ?? ''));
                    $temInscricao = filled($embarcacao->inscricao);
                @endphp

                {{-- Card: Perfil --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md" aria-hidden="true">
                            @include('embarcacoes.partials.icon-tipo-embarcacao', ['tipo' => $embarcacao->tipo, 'svgClass' => 'h-12 w-12'])
                        </div>
                        <span class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide
                            {{ $temInscricao ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200' }}">
                            <span class="h-2 w-2 rounded-full {{ $temInscricao ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                            {{ $temInscricao ? __('Inscrita') : __('Sem inscrição') }}
                        </span>
                        <p class="mt-4 text-sm font-extrabold tracking-wide text-slate-900 dark:text-white">
                            {{ $nomeUpper ?: '—' }}
                        </p>
                        @if ($temInscricao)
                            <p class="mt-2.5 text-xs font-semibold tabular-nums tracking-wide text-slate-700 dark:text-slate-200">
                                <span class="font-normal text-slate-500 dark:text-slate-400">{{ __('Inscrição') }}:</span>
                                {{ $embarcacao->inscricao }}
                            </p>
                        @endif
                        <div class="mt-4 max-w-full px-1 text-center">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Proprietário(a)') }}</p>
                            @if ($embarcacao->cliente)
                                <a href="{{ route('clientes.show', $embarcacao->cliente) }}" class="mt-1 line-clamp-2 block text-sm font-semibold text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400">{{ $embarcacao->cliente->nome }}</a>
                            @else
                                <p class="mt-1 text-sm font-medium text-slate-400 dark:text-slate-500">—</p>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $cpfFmt }}
                        </p>
                    </div>

                    <div class="mt-6 space-y-2 text-sm">
                        <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                            <div class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                {{ __('Processos') }}
                            </div>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                                {{ $embarcacao->processos->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                            <div class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                {{ __('Anexos') }}
                            </div>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-50 px-2 text-xs font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                                {{ $embarcacao->anexos->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl px-2 py-2 text-slate-600 dark:text-slate-300">
                            <div class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18.75c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V9.75L12 3 3 9.75v9Z" /></svg>
                                {{ __('Cliente') }}
                            </div>
                            <span class="inline-flex h-6 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                {{ $embarcacao->cliente ? __('Vinculado') : __('—') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @if ($embarcacao->cliente)
                            <a href="{{ route('clientes.show', $embarcacao->cliente) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:focus:ring-emerald-400/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                {{ __('Ver cliente') }}
                            </a>
                        @endif
                        @can('update', $embarcacao)
                            <a href="{{ route('embarcacoes.edit', $embarcacao) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                {{ __('Alterar cadastro') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div id="servicos-embarcacao" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    @include('embarcacoes.partials.embarcacao-servicos-vinculados', [
                        'embarcacao' => $embarcacao,
                        'modalNovoProcesso' => $mostrarModalNovoProcesso ?? false,
                    ])
                </div>
                </div>
            </aside>

            {{-- Conteúdo --}}
            <div class="space-y-6">
                @if (session('status'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @include('embarcacoes.partials.dados-embarcacao-grid', ['embarcacao' => $embarcacao])

                @php
                    $arquivosDocCount = $embarcacao->anexos->reject(fn ($a) => in_array($a->tipo_codigo, [
                        \App\Support\EmbarcacaoTiposAnexo::FOTO_TRAVES,
                        \App\Support\EmbarcacaoTiposAnexo::FOTO_POPA,
                        \App\Support\EmbarcacaoTiposAnexo::FOTO_OUTRAS,
                    ], true))->count();
                @endphp
                <div class="grid gap-6 lg:grid-cols-2 lg:items-stretch">
                <section id="fotos-embarcacao" class="flex h-full min-h-0 min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white shadow-sm ring-2 ring-teal-600/25 dark:bg-teal-600 dark:ring-teal-400/25" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008H12V8.25Z" />
                                </svg>
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Fotos da embarcação') }}</h3>
                                <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold tabular-nums text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $fotosTotalGaleria }}</span>
                            </div>
                        </div>
                        @can('update', $embarcacao)
                            <a
                                href="#fotos-upload"
                                role="button"
                                class="nx-fotos-adicionar inline-flex items-center gap-1.5 rounded-xl border border-teal-800/20 bg-teal-600 bg-gradient-to-r from-teal-600 to-teal-700 px-4 py-2.5 text-sm font-semibold !text-white shadow-md shadow-teal-600/30 no-underline transition hover:border-teal-900/30 hover:from-teal-500 hover:to-teal-600 hover:!text-white focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:ring-offset-2 focus:!text-white dark:border-teal-300/25 dark:from-teal-600 dark:to-teal-700 dark:shadow-teal-900/40 dark:hover:from-teal-500 dark:hover:to-teal-600 dark:focus:ring-offset-slate-900"
                                @click.prevent="fotosUploadIrEAbrir()"
                                title="{{ __('Ir ao envio de fotos e abrir a seleção de ficheiros (ou arraste imagens para a zona tracejada)') }}"
                            >
                                <span class="text-lg font-light leading-none" aria-hidden="true">+</span>
                                {{ __('Adicionar') }}
                            </a>
                        @endcan
                    </div>
                    <div class="flex flex-1 flex-col gap-4 px-6 py-4 min-h-0">
                        <div class="min-w-0">
                            @include('embarcacoes.partials.fotos-embarcacao-galeria', [
                                'embarcacao' => $embarcacao,
                                'fotosGaleriaPaginator' => $fotosGaleriaPaginator,
                                'mostrarTitulosVazios' => true,
                            ])
                        </div>
                        @include('embarcacoes.partials.fotos-embarcacao-dropzone', ['embarcacao' => $embarcacao])
                    </div>
                </section>

                <section id="arquivos-embarcacao" class="flex h-full min-h-0 min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" aria-labelledby="ficha-arquivos-heading">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-500 text-white shadow-sm ring-2 ring-violet-500/20 dark:bg-violet-600 dark:ring-violet-400/20" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.381-7.69 7.69" />
                                </svg>
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 id="ficha-arquivos-heading" class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Anexos da embarcação') }}</h3>
                                <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold tabular-nums text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $arquivosDocCount }}</span>
                            </div>
                        </div>
                        @can('manage', $embarcacao)
                            <a
                                href="#envio"
                                role="button"
                                class="nx-arquivos-adicionar inline-flex items-center gap-1.5 rounded-xl border border-violet-800/20 bg-violet-600 bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-2.5 text-sm font-semibold !text-white shadow-md shadow-violet-600/30 no-underline transition hover:border-violet-900/30 hover:from-violet-500 hover:to-purple-500 hover:!text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:ring-offset-2 focus:!text-white dark:border-violet-300/25 dark:shadow-violet-900/40 dark:focus:ring-offset-slate-900"
                                @click.prevent="arquivosUploadIrEAbrir()"
                                title="{{ __('Ir ao envio de arquivos e abrir a seleção de ficheiros (ou arraste ficheiros para a zona tracejada)') }}"
                            >
                                <span class="text-lg font-light leading-none" aria-hidden="true">+</span>
                                {{ __('Adicionar') }}
                            </a>
                        @endcan
                    </div>
                    <div class="flex flex-1 flex-col space-y-8 px-6 py-5 min-h-0">
                        @include('embarcacoes.partials.ficha-arquivos-lista', ['embarcacao' => $embarcacao])

                        @can('manage', $embarcacao)
                            <div id="envio" class="border-t border-slate-200 pt-8 dark:border-slate-800">
                                @php
                                    $tipoEmbEnvioOld = (string) old('tipo_codigo', '');
                                    $tipoEmbPreset = match ($tipoEmbEnvioOld) {
                                        \App\Support\EmbarcacaoTiposAnexo::TIE => \App\Support\EmbarcacaoTiposAnexo::TIE,
                                        \App\Support\EmbarcacaoTiposAnexo::SEGURO_DPEM => \App\Support\EmbarcacaoTiposAnexo::SEGURO_DPEM,
                                        default => ($tipoEmbEnvioOld !== '' ? '__outro' : ''),
                                    };
                                    $tipoEmbCustom = $tipoEmbPreset === '__outro' ? $tipoEmbEnvioOld : '';
                                    $envioAnexosMostrarTipo = $errors->has('tipo_codigo_custom');
                                @endphp
                                <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-white">
                                    <form
                                        method="POST"
                                        action="{{ route('embarcacoes.anexos.store', $embarcacao) }}"
                                        enctype="multipart/form-data"
                                        x-data="{
                                            drag: false,
                                            files: [],
                                            preset: @js($tipoEmbPreset),
                                            custom: @js($tipoEmbCustom),
                                            onDragEnter(e) {
                                                e.preventDefault();
                                                this.drag = true;
                                            },
                                            onDragOver(e) {
                                                e.preventDefault();
                                                if (e.dataTransfer) {
                                                    e.dataTransfer.dropEffect = 'copy';
                                                }
                                                this.drag = true;
                                            },
                                            onDragLeave(e) {
                                                const rel = e.relatedTarget;
                                                if (rel && e.currentTarget.contains(rel)) {
                                                    return;
                                                }
                                                this.drag = false;
                                            },
                                            onDrop(e) {
                                                e.preventDefault();
                                                this.drag = false;
                                                const input = this.$refs.fileInputOutro;
                                                const dt = new DataTransfer();
                                                for (const f of e.dataTransfer.files) {
                                                    dt.items.add(f);
                                                }
                                                input.files = dt.files;
                                                this.updateFiles();
                                            },
                                            updateFiles() {
                                                const input = this.$refs.fileInputOutro;
                                                this.files = input && input.files ? Array.from(input.files) : [];
                                            },
                                            clearQueue() {
                                                const input = this.$refs.fileInputOutro;
                                                if (input) {
                                                    input.value = '';
                                                }
                                                this.files = [];
                                            },
                                        }"
                                        x-init="updateFiles()"
                                    >
                                        @csrf
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Arrastar e largar arquivos da embarcação') }}</p>
                                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Largue os arquivos aqui ou clique para escolher. Depois indique o tipo do documento.') }}</p>

                                        <div
                                            class="mt-3 flex min-h-[11rem] cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-6 text-center transition"
                                            :class="drag ? 'border-violet-500 bg-white ring-2 ring-violet-500/20 dark:border-violet-400 dark:bg-white dark:ring-violet-400/25' : 'border-slate-300 bg-white dark:border-slate-500 dark:bg-white'"
                                            @dragenter.prevent="onDragEnter($event)"
                                            @dragover.prevent="onDragOver($event)"
                                            @dragleave="onDragLeave($event)"
                                            @drop.prevent="onDrop($event)"
                                            @click="$refs.fileInputOutro.click()"
                                            role="button"
                                            tabindex="0"
                                            @keydown.enter.prevent="$refs.fileInputOutro.click()"
                                            @keydown.space.prevent="$refs.fileInputOutro.click()"
                                        >
                                            <div class="flex w-full max-w-md flex-col items-center gap-2 pointer-events-none" x-show="files.length === 0" x-cloak>
                                                <svg class="h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.25" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.28 4.5 4.5 0 0 1 4.34 4.476 4.5 4.5 0 0 1-6.335 4.46" />
                                                </svg>
                                                <span class="text-base font-semibold text-slate-800 dark:text-slate-900">{{ __('Largar arquivos ou clicar para selecionar') }}</span>
                                                <span class="text-sm text-slate-500 dark:text-slate-600">{{ __('PDF, JPG, PNG, WebP, Word') }} · {{ __('máx.') }} 10 MB</span>
                                            </div>
                                            <div class="w-full max-w-md px-1 text-left pointer-events-none" x-show="files.length > 0" x-cloak>
                                                <p class="mb-2 text-center text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-600">{{ __('Ficheiros selecionados') }}</p>
                                                <ul class="max-h-36 space-y-1.5 overflow-y-auto text-base">
                                                    <template x-for="(file, idx) in files" :key="idx">
                                                        <li class="truncate rounded-md bg-slate-100 px-2.5 py-1.5 text-left font-medium text-slate-800 dark:bg-slate-200 dark:text-slate-900" x-text="file.name"></li>
                                                    </template>
                                                </ul>
                                                <p class="mt-2 text-center text-sm text-slate-500 dark:text-slate-600">{{ __('Clique para alterar a seleção') }}</p>
                                            </div>
                                            <input
                                                x-ref="fileInputOutro"
                                                type="file"
                                                name="arquivos[]"
                                                multiple
                                                class="sr-only"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/jpeg,image/png,image/webp,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                                @change="updateFiles()"
                                            />
                                        </div>

                                        <x-input-error :messages="$errors->get('arquivos')" class="mt-2" />
                                        <x-input-error :messages="$errors->get('tipo_codigo_custom')" class="mt-2" />

                                        <div class="mt-4 space-y-3" x-show="files.length > 0 || @js($envioAnexosMostrarTipo)" x-cloak>
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                    <span x-text="files.length"></span> {{ __('ficheiro(s) selecionado(s)') }}
                                                </p>
                                                <button type="button" class="text-sm font-semibold text-violet-600 hover:underline dark:text-violet-400" @click="clearQueue()">{{ __('Limpar') }}</button>
                                            </div>
                                            <div class="grid gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="envio-tipo-preset">
                                                        {{ __('Tipo de documento') }}
                                                    </label>
                                                    <select
                                                        id="envio-tipo-preset"
                                                        name="tipo_codigo_preset"
                                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-base shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                                        x-model="preset"
                                                    >
                                                        <option value="" @selected($tipoEmbPreset === '')>{{ __('Selecione…') }}</option>
                                                        <option value="{{ \App\Support\EmbarcacaoTiposAnexo::TIE }}">{{ __('TIE') }}</option>
                                                        <option value="{{ \App\Support\EmbarcacaoTiposAnexo::SEGURO_DPEM }}">{{ __('Seguro DPEM') }}</option>
                                                        <option value="__outro">{{ __('Outro anexo') }}</option>
                                                    </select>
                                                </div>
                                                <div x-show="preset === '__outro'" x-cloak>
                                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="envio-tipo-custom">
                                                        {{ __('Tipo do anexo') }}
                                                    </label>
                                                    <input
                                                        id="envio-tipo-custom"
                                                        type="text"
                                                        name="tipo_codigo_custom"
                                                        x-model="custom"
                                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-base shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                                        maxlength="64"
                                                        placeholder="{{ __('Descreva o documento') }}"
                                                    />
                                                </div>
                                            </div>
                                            <button
                                                type="submit"
                                                class="inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-4 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 dark:focus:ring-violet-400/30"
                                            >
                                                {{ __('Enviar arquivos') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endcan
                    </div>
                </section>
                </div>
            </div>
        </div>
    </div>

    @if ($mostrarModalNovoProcesso ?? false)
        @can('create', \App\Models\Processo::class)
            @include('processos.partials.modal-novo-processo', [
                'tipos' => $tiposProcessoModal,
                'clientesSuggest' => $clientesSuggestProcessoModal,
                'categoriasProcesso' => $categoriasProcesso,
                'categoriaProcessoOld' => $categoriaProcessoOld,
            ])
        @endcan
    @endif
</x-app-layout>
