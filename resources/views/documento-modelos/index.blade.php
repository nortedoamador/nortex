<x-app-layout title="{{ __('Modelos de documentos') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ __('Modelos de documentos') }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('Edite os modelos (ANEXOS) quando houver atualização.') }}
            </p>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-indigo-200/80 bg-white shadow-sm dark:border-indigo-900/40 dark:bg-slate-900">
                <div class="border-b border-indigo-100 bg-indigo-50/80 px-5 py-3 text-sm font-semibold text-indigo-950 dark:border-indigo-900/40 dark:bg-indigo-950/30 dark:text-indigo-100">
                    {{ __('Novo modelo (upload)') }}
                </div>
                <form method="post" action="{{ route('documento-modelos.store') }}" enctype="multipart/form-data" class="space-y-4 p-5">
                    @csrf
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        {{ __('Envie um ficheiro .blade.php, .html ou .txt. O slug identifica o modelo na URL (se vazio, é gerado a partir do título).') }}
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="nx-novo-modelo-titulo" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Título') }}</label>
                            <input
                                id="nx-novo-modelo-titulo"
                                name="titulo"
                                type="text"
                                value="{{ old('titulo') }}"
                                required
                                maxlength="160"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                placeholder="{{ __('Ex.: Meu anexo personalizado') }}"
                            />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <label for="nx-novo-modelo-referencia" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Referência (opcional)') }}</label>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Norma ou instrumento que originou o anexo.') }}</p>
                            <input
                                id="nx-novo-modelo-referencia"
                                name="referencia"
                                type="text"
                                value="{{ old('referencia') }}"
                                maxlength="160"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                placeholder="{{ __('Ex.: NORMAM-212/DPC') }}"
                            />
                            <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
                        </div>
                        <div>
                            <label for="nx-novo-modelo-slug" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug (opcional)') }}</label>
                            <input
                                id="nx-novo-modelo-slug"
                                name="slug"
                                type="text"
                                value="{{ old('slug') }}"
                                maxlength="80"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                placeholder="{{ __('Ex.: meu-anexo-2026') }}"
                            />
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>
                        <div>
                            <label for="nx-novo-modelo-arquivo" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Ficheiro do modelo') }}</label>
                            <input
                                id="nx-novo-modelo-arquivo"
                                name="arquivo"
                                type="file"
                                required
                                accept=".blade.php,.blade,.php,.html,.htm,.txt,text/html,text/plain"
                                class="mt-1 block w-full text-sm text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                            />
                            <x-input-error :messages="$errors->get('arquivo')" class="mt-2" />
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ __('Criar modelo a partir do ficheiro') }}
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-slate-100">
                    {{ __('Modelos disponíveis') }}
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($modelos as $m)
                        <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $m->titulo }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->slug }}</div>
                                @if (filled($m->referencia))
                                    <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ __('Referência') }}: {{ $m->referencia }}</div>
                                @endif
                            </div>
                            <a
                                href="{{ route('documento-modelos.edit', $m) }}"
                                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                {{ __('Editar') }}
                            </a>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Nenhum modelo cadastrado ainda.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

