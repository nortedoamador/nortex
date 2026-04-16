<x-platform-layout :title="__('Documentos automatizados — laboratório')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Documentos automatizados') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Escolha uma empresa, um cliente e, se necessário, uma embarcação para abrir cada anexo e validar o preenchimento com dados reais.') }}</p>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('Os esqueletos globais podem ser criados ou substituídos por ficheiro aqui; use o gestor para editar texto e propagar às empresas.') }}</p>
            </div>
            <a href="{{ route('platform.cadastros.documentos-automatizados.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                {{ __('Lista global') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-indigo-200/80 bg-white shadow-sm dark:border-indigo-900/40 dark:bg-slate-900">
            <div class="border-b border-indigo-100 bg-indigo-50/80 px-4 py-3 text-sm font-semibold text-indigo-950 dark:border-indigo-900/40 dark:bg-indigo-950/30 dark:text-indigo-100 sm:px-6">
                {{ __('Novo modelo (upload)') }}
            </div>
            <form method="post" action="{{ route('platform.cadastros.documentos-automatizados.laboratorio.store-novo') }}" enctype="multipart/form-data" class="space-y-4 p-4 sm:p-6">
                @csrf
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    {{ __('Cria um documento global com slug próprio. Após criar, surge na tabela abaixo (com empresa e cliente selecionados) para pré-visualização e substituição por ficheiro.') }}
                    {{ __('Ficheiros: .blade.php, .html ou .txt.') }}
                </p>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    {{ __('Cada span com data-nx="campo" ou data-campo="campo" (ex.: cpf, nome_embarcacao) usa dados do cliente ou da embarcação na pré-visualização.') }}
                </p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="nx-plat-lab-novo-titulo" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Título') }}</label>
                        <input
                            id="nx-plat-lab-novo-titulo"
                            name="titulo"
                            type="text"
                            value="{{ old('titulo') }}"
                            required
                            maxlength="160"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="nx-plat-lab-novo-referencia" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Referência (opcional)') }}</label>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Norma ou instrumento que originou o anexo.') }}</p>
                        <input
                            id="nx-plat-lab-novo-referencia"
                            name="referencia"
                            type="text"
                            value="{{ old('referencia') }}"
                            maxlength="160"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="{{ __('Ex.: NORMAM-212/DPC') }}"
                        />
                        <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
                    </div>
                    <div>
                        <label for="nx-plat-lab-novo-slug" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Slug (opcional)') }}</label>
                        <input
                            id="nx-plat-lab-novo-slug"
                            name="slug"
                            type="text"
                            value="{{ old('slug') }}"
                            maxlength="80"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        />
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>
                    <div>
                        <label for="nx-plat-lab-novo-arquivo" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Ficheiro') }}</label>
                        <input
                            id="nx-plat-lab-novo-arquivo"
                            name="arquivo"
                            type="file"
                            required
                            accept=".blade.php,.blade,.php,.html,.htm,.txt,text/html,text/plain"
                            class="mt-1 block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2 file:py-1.5 file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                        />
                        <x-input-error :messages="$errors->get('arquivo')" class="mt-2" />
                    </div>
                </div>
                @if ($empresaId && ctype_digit((string) $empresaId))
                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}" />
                @endif
                @if ($clienteId && ctype_digit((string) $clienteId))
                    <input type="hidden" name="cliente_id" value="{{ $clienteId }}" />
                @endif
                @if ($embarcacaoId && ctype_digit((string) $embarcacaoId))
                    <input type="hidden" name="embarcacao_id" value="{{ $embarcacaoId }}" />
                @endif
                <input type="hidden" name="sort" value="{{ $labSort }}" />
                <input type="hidden" name="dir" value="{{ $labDir }}" />
                <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    {{ __('Criar modelo a partir do ficheiro') }}
                </button>
            </form>
        </div>

        <form method="get" action="{{ route('platform.cadastros.documentos-automatizados.laboratorio') }}" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <input type="hidden" name="sort" value="{{ $labSort }}" />
            <input type="hidden" name="dir" value="{{ $labDir }}" />
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="nx-plat-lab-empresa" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Empresa') }}</label>
                    <select name="empresa_id" id="nx-plat-lab-empresa" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800" onchange="this.form.submit()">
                        <option value="">{{ __('Selecione…') }}</option>
                        @foreach ($empresas as $emp)
                            <option value="{{ $emp->id }}" @selected((string) $empresaId === (string) $emp->id)>{{ $emp->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="nx-plat-lab-cliente" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Cliente') }}</label>
                    <select name="cliente_id" id="nx-plat-lab-cliente" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800" @disabled(! $empresaId || ! ctype_digit((string) $empresaId)) onchange="this.form.submit()">
                        <option value="">{{ __('Selecione…') }}</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}" @selected((string) $clienteId === (string) $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="nx-plat-lab-emb" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Embarcação (opcional)') }}</label>
                    <select name="embarcacao_id" id="nx-plat-lab-emb" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800" @disabled($embarcacoes->isEmpty()) onchange="this.form.submit()">
                        <option value="">{{ __('Nenhuma') }}</option>
                        @foreach ($embarcacoes as $e)
                            <option value="{{ $e->id }}" @selected((string) $embarcacaoId === (string) $e->id)>{{ $e->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        @if (! $empresaId || ! ctype_digit((string) $empresaId))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200 space-y-2">
                <p>{{ __('Selecione uma empresa para carregar os clientes e ver os links de pré-visualização.') }}</p>
                @if ($countGlobais > 0)
                    <p>{{ __('Existem :n documento(s) global(is). Depois de escolher empresa e cliente, a tabela mostra pré-visualização e upload por slug.', ['n' => $countGlobais]) }}</p>
                @endif
            </div>
        @elseif (! $clienteId || ! ctype_digit((string) $clienteId))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200 space-y-2">
                <p>{{ __('Selecione um cliente para ver os links de pré-visualização e a tabela de modelos.') }}</p>
                @if ($countGlobais > 0)
                    <p>{{ __('Existem :n documento(s) global(is).', ['n' => $countGlobais]) }}</p>
                @endif
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-1 border-b border-slate-200 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Modelos globais (pré-visualização e ficheiro)') }}</span>
                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ __('Deslize horizontalmente se faltar alguma coluna.') }}</span>
                </div>
                <div class="overflow-x-auto overscroll-x-contain">
                    <table class="min-w-[64rem] w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'slug', 'label' => __('Slug'), 'align' => 'left'])
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'titulo', 'label' => __('Título'), 'align' => 'left'])
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'referencia', 'label' => __('Referência'), 'align' => 'left'])
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'atualizado_em', 'label' => __('Atualizado'), 'align' => 'left'])
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'precisa_embarcacao', 'label' => __('Pré-visualizar'), 'align' => 'right'])
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'tem_modelo', 'label' => __('Ações'), 'align' => 'left'])
                                @include('platform.cadastros.documentos-automatizados.partials.lab-sort-th', ['column' => 'tem_modelo', 'label' => __('Enviar modelo'), 'align' => 'left'])
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($linhasLaboratorio as $linha)
                                @php
                                    $slug = $linha['slug'];
                                    $dm = $linha['modelo'];
                                    $precisaEmb = $precisaContexto($slug);
                                    $previewQuery = array_filter([
                                        'empresa_id' => (int) $empresaId,
                                        'cliente_id' => (int) $clienteId,
                                        'slug' => $slug,
                                        'format' => 'html',
                                        'embarcacao_id' => ($precisaEmb && $embarcacaoId && ctype_digit((string) $embarcacaoId)) ? (int) $embarcacaoId : null,
                                    ], static fn ($v) => $v !== null && $v !== '');
                                    $urlHtml = route('platform.cadastros.documentos-automatizados.preview', $previewQuery);
                                    $previewQueryPdf = array_merge($previewQuery, ['format' => 'pdf']);
                                    $urlPdf = route('platform.cadastros.documentos-automatizados.preview', $previewQueryPdf);
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $slug }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $linha['titulo'] }}</td>
                                    <td class="max-w-[12rem] px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                        @if (filled($linha['referencia'] ?? null))
                                            {{ $linha['referencia'] }}
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">
                                        @if ($dm && $dm->updated_at)
                                            {{ $dm->updated_at->format('d/m/y H:i') }}
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm space-x-2">
                                        <a href="{{ $urlHtml }}" target="_blank" rel="noopener" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('HTML') }}</a>
                                        <a href="{{ $urlPdf }}" target="_blank" rel="noopener" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('PDF') }}</a>
                                        @if ($precisaEmb && ! $embarcacaoId)
                                            <span class="text-xs text-amber-600 dark:text-amber-400" title="{{ __('Este modelo usa dados da embarcação') }}">{{ __('precisa embarcação') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 align-top text-sm">
                                        <a
                                            href="{{ route('platform.cadastros.documentos-automatizados.edit', $dm) }}"
                                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                        >{{ __('Editar no gestor') }}</a>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <form
                                            method="post"
                                            action="{{ route('platform.cadastros.documentos-automatizados.laboratorio.upload') }}"
                                            enctype="multipart/form-data"
                                            class="flex min-w-[14rem] flex-col gap-2 sm:max-w-xs"
                                        >
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $slug }}" />
                                            <input type="hidden" name="empresa_id" value="{{ $empresaId }}" />
                                            <input type="hidden" name="cliente_id" value="{{ $clienteId }}" />
                                            @if ($embarcacaoId && ctype_digit((string) $embarcacaoId))
                                                <input type="hidden" name="embarcacao_id" value="{{ $embarcacaoId }}" />
                                            @endif
                                            <input type="hidden" name="sort" value="{{ $labSort }}" />
                                            <input type="hidden" name="dir" value="{{ $labDir }}" />
                                            <label for="nx-plat-lab-up-{{ $loop->index }}" class="sr-only">{{ __('Ficheiro do modelo') }} ({{ $slug }})</label>
                                            <input
                                                id="nx-plat-lab-up-{{ $loop->index }}"
                                                type="file"
                                                name="arquivo"
                                                accept=".blade.php,.blade,.php,.html,.htm,.txt,text/html,text/plain"
                                                class="block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                                            />
                                            <button type="submit" class="inline-flex w-fit items-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
                                                {{ __('Substituir por ficheiro') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-platform-layout>
