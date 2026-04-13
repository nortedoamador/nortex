<x-tenant-admin-layout title="{{ __('Verificação do modelo') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ __('Verificação do modelo') }} — {{ $modelo->titulo }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('Mapeamento automático do último upload e variáveis Blade detetadas no HTML.') }}
            </p>
        </div>
    </x-slot>

    @php
        $labQuery = array_filter(request()->only('cliente_id', 'embarcacao_id', 'sort', 'dir'), fn ($v) => $v !== null && $v !== '');
        $confirmarMapeamentoUrl = tenant_doc_modelo_route('confirmar-mapeamento-upload', ['modelo' => $modelo]);
        if (count($labQuery) > 0) {
            $confirmarMapeamentoUrl .= '?'.http_build_query($labQuery);
        }
    @endphp

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (! empty($mapeamentoPendente))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100">
                    <p class="font-semibold">{{ __('Mapeamento por confirmar') }}</p>
                    <p class="mt-1 text-xs text-amber-900/90 dark:text-amber-200/90">
                        {{ __('À esquerda está o ficheiro tal como foi enviado. À direita, a pré-visualização com variáveis Blade e comentários de referência. A base de dados e o ficheiro em disco só passam a usar o mapeamento após confirmar.') }}
                    </p>
                    @if ($podeEditarModelo)
                        <form method="post" action="{{ $confirmarMapeamentoUrl }}" class="mt-3">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-full border border-amber-700 bg-amber-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-800 dark:border-amber-600 dark:bg-amber-600 dark:hover:bg-amber-500">
                                {{ __('Confirmar mapeamento e gravar modelo') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                @if ($podeEditarModelo)
                    <a href="{{ tenant_doc_modelo_route('edit', ['modelo' => $modelo]) }}" class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-900 hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-950/40 dark:text-indigo-200 dark:hover:bg-indigo-950/60">
                        {{ __('Ir para edição') }}
                    </a>
                @endif
                <a href="{{ tenant_admin_route('documento-modelos.laboratorio', $labQuery) }}" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Laboratório') }}
                </a>
                @if ($urlPreviewHtml)
                    <a href="{{ $urlPreviewHtml }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('Pré-visualizar HTML') }}
                    </a>
                @endif
                @if ($urlPreviewPdf)
                    <a href="{{ $urlPreviewPdf }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('Descarregar PDF') }}
                    </a>
                @endif
            </div>

            @if (! empty($mapeamentoPendente) && ($urlPreviewHtml || $urlPreviewPdf))
                <p class="text-xs text-amber-800 dark:text-amber-200/90">
                    {{ __('Nota: com o mapeamento por confirmar, a pré-visualização usa ainda o HTML bruto — os dados do cliente podem não aparecer até confirmar.') }}
                </p>
            @endif

            @if ($geradoEm)
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ __('Mapeamento do upload gerado em: :t', ['t' => $geradoEm]) }}
                </p>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-3 dark:border-slate-800">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Blade completo (tal como no upload)') }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Texto exactamente como veio do ficheiro enviado (sem substituição automática de spans). Se já confirmou o mapeamento, corresponde ao último upload guardado para referência.') }}</p>
                    </div>
                    <div class="p-4">
                        <textarea
                            readonly
                            rows="18"
                            class="h-[min(70vh,42rem)] w-full resize-y rounded-lg border border-slate-200 bg-slate-50 p-3 font-mono text-[11px] leading-relaxed text-slate-800 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                            spellcheck="false"
                        >{{ $bladeArmazenadoCompleto }}</textarea>
                    </div>
                </div>
                <div class="overflow-hidden rounded-2xl border border-indigo-200 bg-white shadow-sm dark:border-indigo-900/40 dark:bg-slate-900">
                    <div class="border-b border-indigo-100 bg-indigo-50/80 px-5 py-3 dark:border-indigo-900/50 dark:bg-indigo-950/30">
                        <h3 class="text-sm font-semibold text-indigo-950 dark:text-indigo-100">{{ __('Pré-visualização com variáveis e referências') }}</h3>
                        <p class="mt-1 text-xs text-indigo-800/90 dark:text-indigo-200/80">
                            {{ __('Resultado do mapeamento automático (spans → variáveis Blade, preamble 5-D e CSS de impressão se aplicável). Após cada') }}
                            <span class="font-mono">@{{ $variável }}</span>
                            {{ __('foi acrescentado um comentário Blade com a fonte dos dados. Só passa a ser o modelo oficial na BD e em disco após «Confirmar mapeamento».') }}
                        </p>
                    </div>
                    <div class="p-4">
                        <textarea
                            readonly
                            rows="18"
                            class="h-[min(70vh,42rem)] w-full resize-y rounded-lg border border-slate-200 bg-slate-50 p-3 font-mono text-[11px] leading-relaxed text-slate-800 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                            spellcheck="false"
                        >{{ $bladeComReferenciasCompleto }}</textarea>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-slate-100">
                    {{ __('Interpretação automática (spans → variáveis)') }}
                </div>
                <div class="overflow-x-auto p-4">
                    @if (count($itens) === 0)
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Nenhuma substituição automática neste upload (sem spans elegíveis ou HTML sem &lt;span&gt;).') }}</p>
                    @else
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Tipo') }}</th>
                                    <th class="px-3 py-2">{{ __('Texto no ficheiro') }}</th>
                                    <th class="px-3 py-2">{{ __('Variável') }}</th>
                                    <th class="px-3 py-2">{{ __('Origem') }}</th>
                                    <th class="px-3 py-2">{{ __('Fonte nos dados') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($itens as $row)
                                    @php
                                        $tipo = $row['tipo'] ?? '';
                                        $tipoLabel = match ($tipo) {
                                            'explicito' => __('Explícito (data-nx / data-campo)'),
                                            'padrao_pdf24' => __('Padrão em span PDF24'),
                                            'alias' => __('Alias de rótulo'),
                                            default => $tipo,
                                        };
                                    @endphp
                                    <tr class="text-slate-800 dark:text-slate-200">
                                        <td class="px-3 py-2 align-top text-xs">{{ $tipoLabel }}</td>
                                        <td class="px-3 py-2 align-top font-mono text-xs">{{ $row['texto_antes'] ?? '—' }}</td>
                                        <td class="px-3 py-2 align-top font-mono text-xs">${{ $row['variavel'] ?? '?' }}</td>
                                        <td class="px-3 py-2 align-top text-xs">
                                            {{ $row['origem'] ?? '—' }}
                                            @if (! empty($row['atributos_span']))
                                                <span class="block text-slate-500 dark:text-slate-400">{{ $row['atributos_span'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 align-top text-xs">{{ \App\Support\DocumentoModeloVariavelLabels::labelPara((string) ($row['variavel'] ?? '')) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-slate-100">
                    {{ __('Variáveis Blade no conteúdo') }} <span class="font-mono font-normal">@{{ $nome }}</span>
                </div>
                <div class="overflow-x-auto p-4 space-y-3">
                    @if (count($scan['variaveis']) === 0)
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Nenhuma variável no padrão') }} <span class="font-mono">@{{ $nome }}</span> {{ __('foi detetada (ou apenas variáveis de sistema foram ignoradas).') }}</p>
                    @else
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Variável') }}</th>
                                    <th class="px-3 py-2">{{ __('Na lista NORMAM') }}</th>
                                    <th class="px-3 py-2">{{ __('Descrição') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($scan['variaveis'] as $v)
                                    <tr class="text-slate-800 dark:text-slate-200">
                                        <td class="px-3 py-2 font-mono text-xs">${{ $v['nome'] }}</td>
                                        <td class="px-3 py-2 text-xs">{{ $v['em_normam'] ? __('Sim') : __('Não') }}</td>
                                        <td class="px-3 py-2 text-xs">{{ \App\Support\DocumentoModeloVariavelLabels::labelPara($v['nome']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if (count($scan['orfas']) > 0)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                            <p class="font-semibold">{{ __('Variáveis não reconhecidas na lista NORMAM') }}</p>
                            <p class="mt-1 font-mono text-xs">{{ implode(', ', array_map(fn ($n) => '$'.$n, $scan['orfas'])) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-tenant-admin-layout>
