<x-tenant-admin-layout title="{{ __('Documentos automatizados') }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Documentos automatizados') }}</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Escolha um cliente e, se necessário, uma embarcação para abrir cada anexo e validar o preenchimento.') }}</p>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('Os esqueletos globais podem ser personalizados por ficheiro ou no gestor; «Repor esqueleto global» volta ao conteúdo da plataforma.') }}</p>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
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
                <form method="post" action="{{ tenant_admin_route('documento-modelos.laboratorio.store-novo') }}" enctype="multipart/form-data" class="space-y-4 p-4 sm:p-6">
                    @csrf
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        {{ __('Cria um modelo com slug próprio. Com cliente selecionado, o modelo surge na tabela abaixo para pré-visualização e envio de ficheiro.') }}
                        {{ __('Ficheiros: .blade.php, .html ou .txt.') }}
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        {{ __('Ao gravar (upload ou edição do modelo), cada span com data-nx="campo" ou data-campo="campo" (ex.: cpf, nome_embarcacao) passa a usar dados do cliente ou da embarcação na pré-visualização. Spans só com texto curto (ex.: CPF, Nome, Cidade) são mapeados quando coincidem com rótulos conhecidos; traços e sublinhados só como valor visual não são alterados.') }}
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="nx-lab-novo-titulo" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Título') }}</label>
                            <input
                                id="nx-lab-novo-titulo"
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
                            <label for="nx-lab-novo-referencia" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Referência (opcional)') }}</label>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Norma ou instrumento que originou o anexo.') }}</p>
                            <input
                                id="nx-lab-novo-referencia"
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
                            <label for="nx-lab-novo-slug" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Slug (opcional)') }}</label>
                            <input
                                id="nx-lab-novo-slug"
                                name="slug"
                                type="text"
                                value="{{ old('slug') }}"
                                maxlength="80"
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            />
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>
                        <div>
                            <label for="nx-lab-novo-arquivo" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Ficheiro') }}</label>
                            <input
                                id="nx-lab-novo-arquivo"
                                name="arquivo"
                                type="file"
                                required
                                accept=".blade.php,.blade,.php,.html,.htm,.txt,text/html,text/plain"
                                class="mt-1 block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2 file:py-1.5 file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                            />
                            <x-input-error :messages="$errors->get('arquivo')" class="mt-2" />
                        </div>
                    </div>
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

            <form method="get" action="{{ tenant_admin_route('documento-modelos.laboratorio') }}" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <input type="hidden" name="sort" value="{{ $labSort }}" />
                <input type="hidden" name="dir" value="{{ $labDir }}" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nx-lab-cliente" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Cliente') }}</label>
                        <select name="cliente_id" id="nx-lab-cliente" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800" onchange="this.form.submit()">
                            <option value="">{{ __('Selecione…') }}</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}" @selected((string) $clienteId === (string) $c->id)>{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nx-lab-emb" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Embarcação (opcional)') }}</label>
                        <select name="embarcacao_id" id="nx-lab-emb" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-800" @disabled($embarcacoes->isEmpty()) onchange="this.form.submit()">
                            <option value="">{{ __('Nenhuma') }}</option>
                            @foreach ($embarcacoes as $e)
                                <option value="{{ $e->id }}" @selected((string) $embarcacaoId === (string) $e->id)>{{ $e->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            @if (! $clienteId || ! ctype_digit((string) $clienteId))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200 space-y-2">
                    <p>{{ __('Selecione um cliente para ver os links de pré-visualização e a tabela de modelos.') }}</p>
                    @if ($countModelosSoCatalogo > 0)
                        <p>
                            {{ __('A sua empresa tem :n modelo(s) com slug próprio (além dos anexos NORMAM). Selecione um cliente acima para os ver na tabela com pré-visualização.', ['n' => $countModelosSoCatalogo]) }}
                        </p>
                    @endif
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col gap-1 border-b border-slate-200 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Modelos (pré-visualização e ficheiro)') }}</span>
                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ __('Deslize horizontalmente se faltar alguma coluna.') }}</span>
                    </div>
                    <div class="overflow-x-auto overscroll-x-contain">
                    <table class="min-w-[64rem] w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'slug', 'label' => __('Slug'), 'align' => 'left'])
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'titulo', 'label' => __('Título'), 'align' => 'left'])
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'referencia', 'label' => __('Referência'), 'align' => 'left'])
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Estado') }}</th>
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'atualizado_em', 'label' => __('Atualizado'), 'align' => 'left'])
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'precisa_embarcacao', 'label' => __('Pré-visualizar'), 'align' => 'right'])
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'tem_modelo', 'label' => __('Ações'), 'align' => 'left'])
                                @include('admin.documento-modelos.partials.lab-sort-th', ['column' => 'tem_modelo', 'label' => __('Enviar modelo'), 'align' => 'left'])
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @php
                                $labClienteModel = \App\Models\Cliente::query()->find((int) $clienteId);
                            @endphp
                            @foreach ($linhasLaboratorio as $linha)
                                @php
                                    $slug = $linha['slug'];
                                    $dm = $linha['modelo'];
                                    $precisaEmb = $precisaContexto($slug);
                                    $q = ['format' => 'html'];
                                    if ($precisaEmb && $embarcacaoId && ctype_digit((string) $embarcacaoId)) {
                                        $q['contexto_id'] = (int) $embarcacaoId;
                                    }
                                    $urlHtml = $labClienteModel
                                        ? route('clientes.documento-modelos.render', ['cliente' => $labClienteModel, 'slug' => $slug]).'?'.http_build_query($q)
                                        : '#';
                                    $qPdf = array_merge($q, ['format' => 'pdf']);
                                    $urlPdf = $labClienteModel
                                        ? route('clientes.documento-modelos.render', ['cliente' => $labClienteModel, 'slug' => $slug]).'?'.http_build_query($qPdf)
                                        : '#';
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
                                    <td class="px-4 py-3 text-xs">
                                        @if (! empty($linha['documento_modelo_global_id']))
                                            @if (! empty($linha['personalizado']))
                                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">{{ __('Personalizado') }}</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-200">{{ __('Igual ao global') }}</span>
                                            @endif
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
                                        <div class="flex flex-col gap-2">
                                            @if ($dm && \App\Support\TenantEmpresaContext::canEditDocumentoModeloConteudo(auth()->user(), request()))
                                                <a
                                                    href="{{ tenant_doc_modelo_route('edit', ['modelo' => $dm]) }}"
                                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                                >{{ __('Editar no gestor') }}</a>
                                            @endif
                                            @if ($dm && ! empty($linha['documento_modelo_global_id']) && ! empty($linha['personalizado']))
                                                <form
                                                    method="post"
                                                    action="{{ tenant_admin_route('documento-modelos.laboratorio.repor-global') }}"
                                                    class="inline"
                                                    onsubmit="return confirm(@js(__('Repor o conteúdo a partir do documento automático global? Perde as alterações locais deste slug.')))"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="slug" value="{{ $slug }}" />
                                                    <input type="hidden" name="cliente_id" value="{{ $clienteId }}" />
                                                    @if ($embarcacaoId && ctype_digit((string) $embarcacaoId))
                                                        <input type="hidden" name="embarcacao_id" value="{{ $embarcacaoId }}" />
                                                    @endif
                                                    <input type="hidden" name="sort" value="{{ $labSort }}" />
                                                    <input type="hidden" name="dir" value="{{ $labDir }}" />
                                                    <button type="submit" class="text-xs font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">
                                                        {{ __('Repor esqueleto global') }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($dm)
                                                <form
                                                    method="post"
                                                    action="{{ tenant_admin_route('documento-modelos.laboratorio.destroy', ['modelo' => $dm]) }}"
                                                    class="inline"
                                                    onsubmit="return confirm(@js(__('Remover este modelo da empresa? Deixa de aparecer na lista de documentos automatizados. Para voltar a ver o anexo no catálogo NORMAM, envie de novo um ficheiro por «Substituir por ficheiro».')))"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="cliente_id" value="{{ $clienteId }}" />
                                                    @if ($embarcacaoId && ctype_digit((string) $embarcacaoId))
                                                        <input type="hidden" name="embarcacao_id" value="{{ $embarcacaoId }}" />
                                                    @endif
                                                    <input type="hidden" name="sort" value="{{ $labSort }}" />
                                                    <input type="hidden" name="dir" value="{{ $labDir }}" />
                                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-500 dark:text-red-400">
                                                        {{ __('Excluir') }}
                                                    </button>
                                                </form>
                                            @elseif (! empty($catalogSlugsLookup[$slug] ?? null))
                                                <form
                                                    method="post"
                                                    action="{{ tenant_admin_route('documento-modelos.laboratorio.ocultar-catalogo') }}"
                                                    class="inline"
                                                    onsubmit="return confirm(@js(__('Remover este anexo do catálogo na sua empresa? Deixa de aparecer na lista. Para voltar a mostrá-lo, use «Substituir por ficheiro» neste slug.')))"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="slug" value="{{ $slug }}" />
                                                    <input type="hidden" name="cliente_id" value="{{ $clienteId }}" />
                                                    @if ($embarcacaoId && ctype_digit((string) $embarcacaoId))
                                                        <input type="hidden" name="embarcacao_id" value="{{ $embarcacaoId }}" />
                                                    @endif
                                                    <input type="hidden" name="sort" value="{{ $labSort }}" />
                                                    <input type="hidden" name="dir" value="{{ $labDir }}" />
                                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-500 dark:text-red-400">
                                                        {{ __('Excluir') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <form
                                            method="post"
                                            action="{{ tenant_admin_route('documento-modelos.laboratorio.upload') }}"
                                            enctype="multipart/form-data"
                                            class="flex min-w-[14rem] flex-col gap-2 sm:max-w-xs"
                                        >
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $slug }}" />
                                            <input type="hidden" name="cliente_id" value="{{ $clienteId }}" />
                                            @if ($embarcacaoId && ctype_digit((string) $embarcacaoId))
                                                <input type="hidden" name="embarcacao_id" value="{{ $embarcacaoId }}" />
                                            @endif
                                            <input type="hidden" name="sort" value="{{ $labSort }}" />
                                            <input type="hidden" name="dir" value="{{ $labDir }}" />
                                            <label for="nx-lab-up-{{ $loop->index }}" class="sr-only">{{ __('Ficheiro do modelo') }} ({{ $slug }})</label>
                                            <input
                                                id="nx-lab-up-{{ $loop->index }}"
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
    </div>
</x-tenant-admin-layout>
