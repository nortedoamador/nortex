<x-app-layout :title="__('Dados da empresa')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Dados da empresa') }}</h1>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Dados cadastrais, procuração e agenda.') }}</p>
                </div>
            </div>

            @php
            $cidadeVal = old('cidade', $empresa->cidade ?? '');
            $textoProcValor = old(
                'texto_procuracao_procuradores',
                filled(trim((string) ($empresa->texto_procuracao_procuradores ?? '')))
                    ? $empresa->texto_procuracao_procuradores
                    : $textoProcuracaoPadrao
            );
            $compromissoErrorKeys = ['tipo', 'tipo_custom', 'titulo', 'data', 'hora_inicio', 'hora_fim', 'local', 'observacoes'];
            $tabsValidas = ['geral', 'procuracao', 'agenda'];
            if ($errors->hasAny($compromissoErrorKeys)) {
                $abaEmpresaInicial = 'agenda';
            } elseif ($errors->has('texto_procuracao_procuradores')) {
                $abaEmpresaInicial = 'procuracao';
            } else {
                $q = request()->query('tab');
                if (is_string($q) && in_array($q, $tabsValidas, true)) {
                    $abaEmpresaInicial = $q;
                } elseif (session()->has('aba_empresa')) {
                    $abaEmpresaInicial = session('aba_empresa');
                } else {
                    $abaEmpresaInicial = 'geral';
                }
            }

            $rNome = trim((string) old('nome', $empresa->nome ?? ''));
            $rCnpjDig = \App\Support\DocumentoBrasil::apenasDigitos((string) old('cnpj', $empresa->cnpj ?? ''));
            $resumoIdentificacaoOk = $rNome !== '' && strlen($rCnpjDig) === 14 && \App\Support\DocumentoBrasil::cnpjValido($rCnpjDig);

            $rFantasia = trim((string) old('nome_fantasia', $empresa->nome_fantasia ?? ''));
            $resumoFiscalExtraOk = $rFantasia !== '';

            $rEmail = trim((string) old('email_contato', $empresa->email_contato ?? ''));
            $rTel = trim((string) old('telefone', $empresa->telefone ?? ''));
            $resumoContatoOk = $rEmail !== '' && $rTel !== '';

            $rCepDig = \App\Support\DocumentoBrasil::apenasDigitos((string) old('cep', $empresa->cep ?? ''));
            $rEndereco = trim((string) old('endereco', $empresa->endereco ?? ''));
            $rNumero = trim((string) old('numero', $empresa->numero ?? ''));
            $rBairro = trim((string) old('bairro', $empresa->bairro ?? ''));
            $rCidadeForm = trim((string) old('cidade', $empresa->cidade ?? ''));
            $rUf = trim((string) old('uf', $empresa->uf ?? ''));
            $resumoEnderecoOk = strlen($rCepDig) === 8
                && $rEndereco !== ''
                && $rNumero !== ''
                && $rBairro !== ''
                && $rCidadeForm !== ''
                && strlen($rUf) === 2;

            $resumoLogoOk = filled($empresa->logo_path);

            $resumoLinhas = [
                ['label' => __('Identificação (razão social e CNPJ válido)'), 'ok' => $resumoIdentificacaoOk],
                ['label' => __('Nome fantasia'), 'ok' => $resumoFiscalExtraOk, 'opcional' => true],
                ['label' => __('Contacto (e-mail e telefone)'), 'ok' => $resumoContatoOk],
                ['label' => __('Endereço da sede (CEP, logradouro, n.º, bairro, cidade e UF)'), 'ok' => $resumoEnderecoOk],
                ['label' => __('Logótipo carregado'), 'ok' => $resumoLogoOk, 'opcional' => true],
            ];
            $resumoCompleto = $resumoIdentificacaoOk && $resumoContatoOk && $resumoEnderecoOk;

            $resumoCompactoCnpj = (strlen($rCnpjDig) === 14 && \App\Support\DocumentoBrasil::cnpjValido($rCnpjDig))
                ? \App\Support\DocumentoBrasil::formatarCnpj($rCnpjDig)
                : '—';
            $resumoCompactoContato = $rEmail !== '' && $rTel !== ''
                ? $rEmail.' · '.$rTel
                : ($rEmail !== '' ? $rEmail : ($rTel !== '' ? $rTel : '—'));
            $cepFmtCompacto = strlen($rCepDig) === 8
                ? substr($rCepDig, 0, 5).'-'.substr($rCepDig, 5)
                : '';
            $partesEndCompacto = array_values(array_filter([
                $cepFmtCompacto !== '' ? 'CEP '.$cepFmtCompacto : '',
                trim(implode(', ', array_filter([$rEndereco, $rNumero !== '' ? __('n.º').' '.$rNumero : '']))),
                $rBairro,
                trim($rCidadeForm.'/'.$rUf),
            ], static fn ($v) => $v !== null && $v !== ''));
            $resumoCompactoEndereco = $partesEndCompacto !== [] ? implode(' · ', $partesEndCompacto) : '—';
            $resumoCompactoFantasia = $rFantasia !== '' ? $rFantasia : null;

            $geralErrorKeys = ['nome', 'nome_fantasia', 'cnpj', 'email_contato', 'telefone', 'cep', 'endereco', 'numero', 'complemento', 'bairro', 'uf', 'cidade', 'logo'];
            $editandoGeralInicial = ! $resumoCompleto || $errors->hasAny($geralErrorKeys);
            if ($resumoCompleto && request()->boolean('editar_geral')) {
                $editandoGeralInicial = true;
            }
            $abrirModalCompromissoErro = $errors->hasAny($compromissoErrorKeys);

            @endphp

            <div
            id="nx-empresa-edit-root"
            class="mx-auto max-w-3xl space-y-4"
            x-data="{
                resumoCompleto: @json($resumoCompleto),
                editandoGeral: @json($editandoGeralInicial),
                cancelarEdicaoGeral() {
                    this.editandoGeral = false;
                    const det = this.$refs.detailsGeral;
                    if (det) {
                        det.open = false;
                    }
                    const u = new URL(window.location.href);
                    u.searchParams.delete('editar_geral');
                    if (!u.searchParams.get('tab')) {
                        u.searchParams.set('tab', 'geral');
                    }
                    window.history.replaceState({}, '', u.pathname + u.search + u.hash);
                },
            }"
            >
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
            @endif
            <form
                method="POST"
                action="{{ route('admin.empresa.update') }}"
                enctype="multipart/form-data"
                class="space-y-4"
                @submit="$refs.geralFieldset && ($refs.geralFieldset.disabled = false)"
                data-cliente-ficha
                data-capitais='@json(\App\Support\BrasilCapitais::porUf())'
                data-msg-selecione-municipio="{{ __('Selecione o município') }}"
                data-geo-cep="empresa_cep"
                data-geo-endereco="empresa_endereco"
                data-geo-bairro="empresa_bairro"
                data-geo-complemento="empresa_complemento"
            >
                @csrf
                @method('PATCH')

                @php
                    $nxEmpNavUser = auth()->user();
                @endphp
                <nav class="flex flex-wrap gap-2" aria-label="{{ __('Secções do formulário') }}">
                    <a
                        href="{{ route('admin.empresa.edit', ['tab' => 'geral']) }}"
                        class="inline-flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition {{ $abaEmpresaInicial === 'geral' ? 'border-slate-200 bg-indigo-600 text-white dark:border-slate-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}"
                    >{{ __('Dados gerais') }}</a>
                    <a
                        href="{{ route('admin.empresa.edit', ['tab' => 'procuracao']) }}"
                        class="inline-flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition {{ $abaEmpresaInicial === 'procuracao' ? 'border-slate-200 bg-indigo-600 text-white dark:border-slate-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}"
                    >{{ __('Procuração') }}</a>
                    <a
                        href="{{ route('admin.empresa.edit', ['tab' => 'agenda']) }}"
                        class="inline-flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition {{ $abaEmpresaInicial === 'agenda' ? 'border-slate-200 bg-indigo-600 text-white dark:border-slate-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}"
                    >{{ __('Agenda') }}</a>
                    @if ($nxEmpNavUser?->hasPermission('usuarios.manage'))
                        <a
                            href="{{ route('equipe.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition {{ request()->routeIs('equipe.*') ? 'border-slate-200 bg-indigo-600 text-white dark:border-slate-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}"
                        >{{ __('Equipe') }}</a>
                    @endif
                    @if ($nxEmpNavUser?->hasPermission('auditoria.view'))
                        <a
                            href="{{ route('admin.auditoria.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition {{ request()->routeIs('admin.auditoria.*') ? 'border-slate-200 bg-indigo-600 text-white dark:border-slate-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}"
                        >{{ __('Auditoria') }}</a>
                    @endif
                </nav>

                <div id="nx-empresa-painel-geral" class="{{ $abaEmpresaInicial !== 'geral' ? 'hidden' : '' }} rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    @if ($resumoCompleto)
                        <details
                            x-ref="detailsGeral"
                            class="group rounded-xl border border-slate-200/80 bg-slate-50/90 dark:border-slate-700 dark:bg-slate-950/50"
                            @if ($editandoGeralInicial) open @endif
                        >
                            <summary
                                class="flex list-none items-start gap-3 rounded-xl px-4 py-3 marker:hidden [&::-webkit-details-marker]:hidden"
                                :class="resumoCompleto && !editandoGeral ? 'cursor-default' : 'cursor-pointer'"
                                @click="if (resumoCompleto && !editandoGeral) { $event.preventDefault(); }"
                            >
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Resumo do cadastro') }}</p>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">{{ __('Completo') }}</span>
                                    </div>
                                    <p class="text-sm font-semibold leading-snug text-slate-900 dark:text-white">{{ $rNome !== '' ? $rNome : '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('CNPJ') }}:</span>
                                        {{ $resumoCompactoCnpj }}
                                        @if ($resumoCompactoFantasia)
                                            <span class="text-slate-400">·</span>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Nome fantasia') }}:</span>
                                            {{ $resumoCompactoFantasia }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Contacto') }}:</span>
                                        {{ $resumoCompactoContato }}
                                    </p>
                                    <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Endereço') }}:</span>
                                        {{ $resumoCompactoEndereco }}
                                    </p>
                                    @if ($resumoLogoOk)
                                        <p class="text-xs text-slate-600 dark:text-slate-400">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Logótipo') }}:</span>
                                            {{ __('Carregado') }}
                                        </p>
                                    @endif
                                </div>
                                <button
                                    type="button"
                                    x-show="resumoCompleto && editandoGeral"
                                    x-cloak
                                    class="mt-1 shrink-0 rounded-lg p-0.5 text-slate-400 transition-transform duration-200 hover:bg-slate-200/80 hover:text-slate-600 group-open:rotate-180 dark:text-slate-500 dark:hover:bg-slate-700 dark:hover:text-slate-300"
                                    @click.stop.prevent="cancelarEdicaoGeral()"
                                    aria-label="{{ __('Cancelar edição') }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                </button>
                                <span
                                    x-show="!resumoCompleto || !editandoGeral"
                                    class="mt-1 shrink-0 text-slate-400 transition-transform duration-200 group-open:rotate-180 dark:text-slate-500"
                                    :class="resumoCompleto && !editandoGeral ? 'pointer-events-none' : ''"
                                    aria-hidden="true"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                </span>
                            </summary>
                            <div class="border-t border-slate-200/80 px-4 pb-3 pt-1 dark:border-slate-700">
                                <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Detalhe do preenchimento') }}</p>
                                <ul class="space-y-2" role="list">
                                    @foreach ($resumoLinhas as $linha)
                                        <li class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-slate-300">
                                            @if ($linha['ok'])
                                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/45 dark:text-emerald-300" title="{{ __('Preenchido') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                                </span>
                                            @else
                                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-dashed border-slate-300 text-slate-400 dark:border-slate-600 dark:text-slate-500" title="{{ __('Pendente') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3 w-3 opacity-70" aria-hidden="true"><circle cx="10" cy="10" r="6" /></svg>
                                                </span>
                                            @endif
                                            <span>
                                                {{ $linha['label'] }}
                                                @if (! empty($linha['opcional']))
                                                    <span class="text-xs font-normal text-slate-400 dark:text-slate-500">({{ __('opcional') }})</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </details>
                        <div
                            x-show="!editandoGeral"
                            class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-slate-200/80 pt-4 dark:border-slate-700"
                        >
                            <a
                                href="{{ route('admin.empresa.edit', ['tab' => 'geral', 'editar_geral' => '1']) }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                            >{{ __('Editar dados da empresa') }}</a>
                        </div>
                    @else
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/90 px-4 py-3 dark:border-slate-700 dark:bg-slate-950/50">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Resumo do cadastro') }}</p>
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">{{ __('Em preenchimento') }}</span>
                            </div>
                            <ul class="mt-3 space-y-2" role="list">
                                @foreach ($resumoLinhas as $linha)
                                    <li class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-slate-300">
                                        @if ($linha['ok'])
                                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/45 dark:text-emerald-300" title="{{ __('Preenchido') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                            </span>
                                        @else
                                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-dashed border-slate-300 text-slate-400 dark:border-slate-600 dark:text-slate-500" title="{{ __('Pendente') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3 w-3 opacity-70" aria-hidden="true"><circle cx="10" cy="10" r="6" /></svg>
                                            </span>
                                        @endif
                                        <span>
                                            {{ $linha['label'] }}
                                            @if (! empty($linha['opcional']))
                                                <span class="text-xs font-normal text-slate-400 dark:text-slate-500">({{ __('opcional') }})</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <fieldset
                        x-ref="geralFieldset"
                        class="min-w-0 space-y-4 border-0 p-0 {{ ($resumoCompleto && ! $editandoGeralInicial) ? 'hidden' : '' }}"
                        :class="{ 'hidden': resumoCompleto && !editandoGeral }"
                        :disabled="resumoCompleto && !editandoGeral"
                    >
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_razao_social">{{ __('Razão social') }}</label>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Denominação registrada na Receita Federal (pessoa jurídica).') }}</p>
                        <input id="empresa_razao_social" name="nome" value="{{ old('nome', $empresa->nome) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_nome_fantasia">{{ __('Nome fantasia') }}</label>
                        <input
                            id="empresa_nome_fantasia"
                            name="nome_fantasia"
                            type="text"
                            value="{{ old('nome_fantasia', $empresa->nome_fantasia) }}"
                            maxlength="255"
                            autocomplete="organization"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('nome_fantasia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_cnpj">{{ __('CNPJ') }}</label>
                        <input
                            id="empresa_cnpj"
                            name="cnpj"
                            type="text"
                            value="{{ old('cnpj', $empresa->cnpj) }}"
                            inputmode="numeric"
                            autocomplete="off"
                            maxlength="18"
                            placeholder="00.000.000/0000-00"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('cnpj')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_email_contato">{{ __('E-mail de contacto') }}</label>
                        <input
                            id="empresa_email_contato"
                            type="email"
                            name="email_contato"
                            value="{{ old('email_contato', $empresa->email_contato) }}"
                            autocomplete="email"
                            inputmode="email"
                            maxlength="255"
                            placeholder="contato@empresa.com"
                            spellcheck="false"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('email_contato')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_telefone">{{ __('Telefone') }}</label>
                        <input
                            id="empresa_telefone"
                            name="telefone"
                            type="tel"
                            value="{{ old('telefone', $empresa->telefone) }}"
                            inputmode="numeric"
                            autocomplete="tel"
                            maxlength="15"
                            placeholder="(00) 00000-0000"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('telefone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('Endereço') }}</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Dados de localização da sede (opcional).') }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_cep">{{ __('CEP') }}</label>
                            <input
                                id="empresa_cep"
                                name="cep"
                                type="text"
                                value="{{ old('cep', $empresa->cep) }}"
                                inputmode="numeric"
                                autocomplete="postal-code"
                                maxlength="9"
                                placeholder="00000-000"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                            @error('cep')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_endereco">{{ __('Logradouro') }}</label>
                            <input
                                id="empresa_endereco"
                                name="endereco"
                                type="text"
                                value="{{ old('endereco', $empresa->endereco) }}"
                                maxlength="255"
                                autocomplete="street-address"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                            @error('endereco')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_numero">{{ __('Número') }}</label>
                            <input
                                id="empresa_numero"
                                name="numero"
                                type="text"
                                value="{{ old('numero', $empresa->numero) }}"
                                maxlength="32"
                                autocomplete="off"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                            @error('numero')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_complemento">{{ __('Complemento') }}</label>
                            <input
                                id="empresa_complemento"
                                name="complemento"
                                type="text"
                                value="{{ old('complemento', $empresa->complemento) }}"
                                maxlength="120"
                                autocomplete="off"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                            @error('complemento')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_bairro">{{ __('Bairro') }}</label>
                        <input
                            id="empresa_bairro"
                            name="bairro"
                            type="text"
                            value="{{ old('bairro', $empresa->bairro) }}"
                            maxlength="120"
                            autocomplete="off"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('bairro')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="uf">{{ __('UF') }}</label>
                            <select
                                id="uf"
                                name="uf"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="" @selected(old('uf', $empresa->uf ?? '') === '')>{{ __('Selecione seu estado') }}</option>
                                @foreach ($ufs as $sigla => $nomeEstado)
                                    <option value="{{ $sigla }}" @selected(old('uf', $empresa->uf ?? '') === $sigla)>{{ $sigla }} — {{ $nomeEstado }}</option>
                                @endforeach
                            </select>
                            @error('uf')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="cidade">{{ __('Cidade') }}</label>
                            <select
                                id="cidade"
                                name="cidade"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="" disabled hidden @selected($cidadeVal === '')>{{ __('Selecione o município') }}</option>
                                @if (filled($cidadeVal))
                                    <option value="{{ $cidadeVal }}" selected>{{ $cidadeVal }}</option>
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Lista conforme a UF (IBGE), igual ao cadastro de clientes.') }}</p>
                            @error('cidade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Logótipo') }}</label>
                        @if ($empresa->logo_path)
                            <p class="mt-1 text-xs text-slate-500">{{ __('Ficheiro atual:') }} {{ $empresa->logo_path }}</p>
                        @endif
                        <input type="file" name="logo" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('O slug da empresa (:s) não é alterado aqui por segurança.', ['s' => $empresa->slug]) }}</p>
                    </fieldset>
                </div>

                <div class="{{ $abaEmpresaInicial !== 'procuracao' ? 'hidden' : '' }} rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="texto_procuracao_procuradores">{{ __('Texto dos procuradores (procuração)') }}</label>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Este parágrafo aparece no modelo de procuração. Se deixar em branco ou guardar o texto igual ao predefinido, é usado o texto padrão da plataforma.') }}</p>
                        <textarea
                            id="texto_procuracao_procuradores"
                            name="texto_procuracao_procuradores"
                            rows="12"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm leading-relaxed dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="{{ __('Edite o parágrafo dos procuradores no modelo de procuração') }}"
                        >{{ $textoProcValor }}</textarea>
                        @error('texto_procuracao_procuradores')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div
                    class="{{ $abaEmpresaInicial !== 'agenda' ? 'hidden' : '' }} rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4"
                    x-data="nxEmpresaAgendaCalendario(@js($compromissosAgendaPayload), @json($abrirModalCompromissoErro))"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Agenda da empresa') }}</h3>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Compromissos manuais, reuniões e dias na Marinha; as aulas criadas na escola náutica aparecem também aqui e no cartão Agenda do dashboard.') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                @click="modalNovoCompromisso = true"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                            >{{ __('Novo compromisso') }}</button>
                            <a
                                href="{{ route('admin.empresa.compromissos.index') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >{{ __('Ver lista completa') }}</a>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-700">
                        <button
                            type="button"
                            @click="prevMonth()"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                            aria-label="{{ __('Mês anterior') }}"
                        >&larr;</button>
                        <p class="min-w-0 flex-1 text-center text-sm font-semibold capitalize text-slate-900 dark:text-white" x-text="monthTitle()"></p>
                        <button
                            type="button"
                            @click="nextMonth()"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                            aria-label="{{ __('Mês seguinte') }}"
                        >&rarr;</button>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <template x-for="h in weekdayHeaders" :key="h">
                            <div class="py-1" x-text="h"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="(cell, idx) in cells()" :key="idx">
                            <div
                                class="min-h-[5.5rem] rounded-lg border p-1 text-left align-top"
                                :class="cell.blank
                                    ? 'border-transparent bg-slate-50/60 dark:bg-slate-900/40'
                                    : (isToday(cell.dateKey)
                                        ? 'border-indigo-400 bg-indigo-50/90 dark:border-indigo-600 dark:bg-indigo-950/35'
                                        : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950')"
                            >
                                <template x-if="!cell.blank">
                                    <div class="flex h-full flex-col">
                                        <span class="text-xs font-semibold tabular-nums text-slate-800 dark:text-slate-100" x-text="cell.day"></span>
                                        <ul class="mt-1 flex flex-1 flex-col gap-0.5 overflow-hidden">
                                            <template x-for="ev in itemsFor(cell.dateKey).slice(0, 3)" :key="ev.id || ev.url">
                                                <li>
                                                    <a
                                                        :href="ev.url"
                                                        :class="(ev.kind || 'compromisso') === 'aula'
                                                            ? 'line-clamp-2 rounded px-0.5 text-[11px] leading-tight text-emerald-700 hover:underline dark:text-emerald-400'
                                                            : 'line-clamp-2 rounded px-0.5 text-[11px] leading-tight text-indigo-600 hover:underline dark:text-indigo-400'"
                                                        :title="(ev.tipo_label ? ev.tipo_label + ' — ' : '') + ev.titulo"
                                                        x-text="(ev.hora ? ev.hora + ' ' : '') + ev.titulo"
                                                    ></a>
                                                </li>
                                            </template>
                                        </ul>
                                        <p
                                            class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500"
                                            x-show="itemsFor(cell.dateKey).length > 3"
                                            x-text="'+' + (itemsFor(cell.dateKey).length - 3) + ' ' + maisLabel"
                                        ></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <template x-teleport="body">
                        <div
                            x-show="modalNovoCompromisso"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                            role="dialog"
                            aria-modal="true"
                            aria-label="{{ __('Novo compromisso') }}"
                        >
                            <div class="absolute inset-0 bg-slate-900/50 dark:bg-black/60" @click="modalNovoCompromisso = false"></div>
                            <div class="relative z-10 w-full max-w-xl max-h-[min(90vh,720px)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-700">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Novo compromisso') }}</h3>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                        @click="modalNovoCompromisso = false"
                                        aria-label="{{ __('Fechar') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                                    </button>
                                </div>
                                <form method="POST" action="{{ route('admin.empresa.compromissos.store') }}" class="mt-4 space-y-4">
                                    @csrf
                                    <input type="hidden" name="return_to" value="empresa_agenda" />
                                    @include('admin.empresa.compromissos._form-fields', ['compromisso' => $compromissoNovoModal, 'tipos' => $compromissosTipos, 'idPrefix' => 'mec'])
                                    <div class="flex flex-wrap gap-3 pt-2">
                                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" @click="modalNovoCompromisso = false">{{ __('Cancelar') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>

                @if ($abaEmpresaInicial === 'procuracao')
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                    </div>
                @elseif ($abaEmpresaInicial === 'geral')
                    <div
                        class="flex flex-wrap items-center gap-3 {{ ($resumoCompleto && ! $editandoGeralInicial) ? 'hidden' : '' }}"
                        :class="{ 'hidden': resumoCompleto && !editandoGeral }"
                    >
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 {{ !($resumoCompleto && $editandoGeralInicial) ? 'hidden' : '' }}"
                            :class="{ 'hidden': !resumoCompleto || !editandoGeral }"
                            @click="cancelarEdicaoGeral()"
                        >{{ __('Cancelar') }}</button>
                    </div>
                @endif
            </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('nxEmpresaAgendaCalendario', (events, abrirModalNovo = false) => ({
                events: Array.isArray(events) ? events : [],
                modalNovoCompromisso: !!abrirModalNovo,
                maisLabel: @js(__('mais')),
                viewYear: new Date().getFullYear(),
                viewMonth: new Date().getMonth() + 1,
                weekdayHeaders: [@js(__('Seg')), @js(__('Ter')), @js(__('Qua')), @js(__('Qui')), @js(__('Sex')), @js(__('Sáb')), @js(__('Dom'))],
                monthTitle() {
                    return new Date(this.viewYear, this.viewMonth - 1, 1).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
                },
                prevMonth() {
                    if (this.viewMonth <= 1) {
                        this.viewMonth = 12;
                        this.viewYear--;
                    } else {
                        this.viewMonth--;
                    }
                },
                nextMonth() {
                    if (this.viewMonth >= 12) {
                        this.viewMonth = 1;
                        this.viewYear++;
                    } else {
                        this.viewMonth++;
                    }
                },
                pad(n) {
                    return String(n).padStart(2, '0');
                },
                cells() {
                    const y = this.viewYear;
                    const m = this.viewMonth;
                    const first = new Date(y, m - 1, 1);
                    let pad = first.getDay() - 1;
                    if (pad < 0) {
                        pad = 6;
                    }
                    const lastDay = new Date(y, m, 0).getDate();
                    const out = [];
                    for (let i = 0; i < pad; i++) {
                        out.push({ blank: true });
                    }
                    for (let d = 1; d <= lastDay; d++) {
                        out.push({
                            blank: false,
                            day: d,
                            dateKey: `${y}-${this.pad(m)}-${this.pad(d)}`,
                        });
                    }
                    while (out.length % 7 !== 0) {
                        out.push({ blank: true });
                    }

                    return out;
                },
                itemsFor(dateKey) {
                    return this.events.filter((e) => e.date === dateKey);
                },
                isToday(dateKey) {
                    const t = new Date();

                    return dateKey === `${t.getFullYear()}-${this.pad(t.getMonth() + 1)}-${this.pad(t.getDate())}`;
                },
            }));
        });
        (() => {
            const onlyDigits = (value) => String(value || '').replace(/\D/g, '');

            const formatCnpj = (value) => {
                const d = onlyDigits(value).slice(0, 14);
                if (d.length <= 2) return d;
                if (d.length <= 5) return `${d.slice(0, 2)}.${d.slice(2)}`;
                if (d.length <= 8) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5)}`;
                if (d.length <= 12) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8)}`;
                return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8, 12)}-${d.slice(12)}`;
            };

            const formatPhoneBr = (value) => {
                const d = onlyDigits(value).slice(0, 11);
                if (d.length === 0) return '';
                if (d.length <= 2) return `(${d}`;
                if (d.length <= 6) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
                if (d.length <= 10) return `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
                return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
            };

            const bindMask = (id, formatter) => {
                const input = document.getElementById(id);
                if (!input) return;
                const apply = () => {
                    const formatted = formatter(input.value);
                    if (formatted !== input.value) {
                        input.value = formatted;
                    }
                };
                input.addEventListener('input', apply);
                input.addEventListener('blur', apply);
                apply();
            };

            const formatCep = (value) => {
                const d = onlyDigits(value).slice(0, 8);
                if (d.length <= 5) return d;
                return `${d.slice(0, 5)}-${d.slice(5)}`;
            };

            bindMask('empresa_cnpj', formatCnpj);
            bindMask('empresa_telefone', formatPhoneBr);
            bindMask('empresa_cep', formatCep);
        })();
    </script>
</x-app-layout>
