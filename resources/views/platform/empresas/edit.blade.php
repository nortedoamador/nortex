<x-platform-layout :title="__('Editar empresa')">
    <x-slot name="header">
        <div>
            <nav class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('platform.empresas.index') }}" class="hover:text-violet-600 dark:hover:text-violet-400">{{ __('Empresas') }}</a>
                <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
                <a href="{{ route('platform.empresas.show', $empresa) }}" class="hover:text-violet-600 dark:hover:text-violet-400">{{ __('Painel') }}</a>
                <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
                <span class="text-slate-700 dark:text-slate-200">{{ __('Dados') }}</span>
            </nav>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar empresa') }} — {{ $empresa->nome }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Mesmos dados gerais que o cadastro no painel da empresa.') }}</p>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('platform.empresas.update', $empresa) }}"
            enctype="multipart/form-data"
            class="space-y-4"
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
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Identificação na plataforma') }}</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }}</label>
                    <input name="slug" value="{{ old('slug', $empresa->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="ativo" value="0" />
                        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $empresa->ativo)) class="rounded border-slate-300 text-violet-600" />
                        {{ __('Empresa ativa') }}
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Acesso à plataforma até') }}</label>
                    <input type="date" name="acesso_plataforma_ate" value="{{ old('acesso_plataforma_ate', $empresa->acesso_plataforma_ate?->format('Y-m-d')) }}" class="mt-1 w-full max-w-xs rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Vazio = sem limite. Inclui o dia indicado; no dia seguinte o acesso tenant é encerrado.') }}</p>
                    @error('acesso_plataforma_ate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Dados gerais') }}</h3>
                </div>

                @include('platform.empresas.partials.dados-gerais', ['empresa' => $empresa, 'ufs' => $ufs])

                @php
                    $pf = config('services.stripe.price_full');
                    $pb = config('services.stripe.price_basic');
                    $podeEscolherPlano = (is_string($pf) && trim($pf) !== '') || (is_string($pb) && trim($pb) !== '');
                    $rotuloPlanoAtual = $empresa->stripePlanLabel() ?? __('(não definido / legado)');
                @endphp
                @if ($podeEscolherPlano)
                    <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Plano de assinatura (Stripe)') }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Plano actual na listagem: :p', ['p' => $rotuloPlanoAtual]) }}</p>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Alterar plano') }}</label>
                            <select name="stripe_plano_referencia" class="mt-1 w-full max-w-md rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="manter" @selected(old('stripe_plano_referencia', 'manter') === 'manter')>{{ __('Manter (não alterar Stripe neste envio)') }}</option>
                                @if (is_string($pb) && trim($pb) !== '')
                                    <option value="essencial" @selected(old('stripe_plano_referencia') === 'essencial')>{{ __('Essencial (STRIPE_PRICE_BASIC)') }}</option>
                                @endif
                                @if (is_string($pf) && trim($pf) !== '')
                                    <option value="completo" @selected(old('stripe_plano_referencia') === 'completo')>{{ __('Completo (STRIPE_PRICE_FULL) — inclui módulo financeiro') }}</option>
                                @endif
                            </select>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('Com subscrição Stripe activa, o sistema tenta actualizar o preço na Stripe (proratação). Sem subscrição, apenas o campo local é actualizado.') }}</p>
                            @error('stripe_plano_referencia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endif
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                <a href="{{ route('platform.empresas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
            </div>
        </form>

        <div class="max-w-3xl space-y-3 pt-8 border-t border-slate-200 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Login do administrador da empresa') }}</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400">{{ __('Cria um utilizador com papel Administrador para a empresa aceder ao sistema (clientes, processos, equipe).') }}</p>
            <form method="POST" action="{{ route('platform.empresas.admin-user.store', $empresa) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4" x-data="{ convite: {{ old('enviar_convite') ? 'true' : 'false' }} }">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                    <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('E-mail (login)') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="enviar_convite" value="1" class="mt-1 rounded border-slate-300 text-violet-600 dark:border-slate-600 dark:bg-slate-900" x-model="convite" />
                        <span>
                            <span class="block text-sm font-medium text-slate-800 dark:text-slate-200">{{ __('Convite por e-mail') }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ __('O utilizador recebe um link para definir a senha (recomendado). Exige SMTP configurado.') }}</span>
                        </span>
                    </label>
                </div>
                <div x-show="!convite" x-cloak class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Senha inicial') }}</label>
                        <input type="password" name="password" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" x-bind:disabled="convite" x-bind:required="!convite" autocomplete="new-password" />
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Confirmar senha') }}</label>
                        <input type="password" name="password_confirmation" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" x-bind:disabled="convite" x-bind:required="!convite" autocomplete="new-password" />
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('O papel «Administrador» é atribuído automaticamente.') }}</p>
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar utilizador') }}</button>
            </form>
        </div>
    </div>
</x-platform-layout>
