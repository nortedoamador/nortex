<x-platform-layout :title="__('Adicionar empresa e assinatura')">
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Adicionar empresa e assinatura') }}</h2>
            <a href="{{ route('platform.assinaturas.index') }}" class="text-sm font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('← Voltar às assinaturas') }}</a>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        @if ($errors->has('geral'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first('geral') }}
            </div>
        @endif

        <form method="POST" action="{{ route('platform.assinaturas.adicionar.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Empresa') }}</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome da empresa') }} *</label>
                        <input name="nome" value="{{ old('nome') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }} <span class="font-normal text-slate-400">({{ __('opcional; gera-se a partir do nome se vazio') }})</span></label>
                        <input name="slug" value="{{ old('slug') }}" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="minha-empresa" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('E-mail de contacto') }} *</label>
                        <input type="email" name="email_contato" value="{{ old('email_contato') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('email_contato')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Telefone') }}</label>
                        <input name="telefone" value="{{ old('telefone') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('telefone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('CNPJ') }}</label>
                        <input name="cnpj" value="{{ old('cnpj') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('cnpj')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('UF') }}</label>
                        <select name="uf" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">{{ __('—') }}</option>
                            @foreach ($ufs as $sigla => $nomeUf)
                                <option value="{{ $sigla }}" @selected(old('uf') === $sigla)>{{ $sigla }} — {{ $nomeUf }}</option>
                            @endforeach
                        </select>
                        @error('uf')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="hidden" name="ativo" value="0" />
                            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', true)) class="rounded border-slate-300 text-violet-600" />
                            {{ __('Empresa ativa') }}
                        </label>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Acesso à plataforma até') }}</label>
                        <input type="date" name="acesso_plataforma_ate" value="{{ old('acesso_plataforma_ate') }}" class="mt-1 max-w-xs rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Opcional. Se definir, os utilizadores desta empresa deixam de aceder ao dia seguinte a esta data. Deixe em branco para sem limite.') }}</p>
                        @error('acesso_plataforma_ate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Administrador da empresa') }}</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome do administrador') }} *</label>
                        <input name="admin_name" value="{{ old('admin_name') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('admin_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('E-mail do administrador') }} *</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('admin_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="enviar_convite" value="1" @checked(old('enviar_convite', true)) class="rounded border-slate-300 text-violet-600" />
                            {{ __('Enviar e-mail para definir senha (recomendado)') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-violet-200/80 bg-violet-50/30 p-6 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-violet-800 dark:text-violet-300">{{ __('Stripe (opcional)') }}</h3>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Pode preencher já ou deixar em branco e editar depois na lista de assinaturas.') }}</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">stripe_customer_id</label>
                        <input name="stripe_customer_id" value="{{ old('stripe_customer_id') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="cus_…" />
                        @error('stripe_customer_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">stripe_subscription_id</label>
                        <input name="stripe_subscription_id" value="{{ old('stripe_subscription_id') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="sub_…" />
                        @error('stripe_subscription_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Estado da subscrição') }}</label>
                        <select name="stripe_subscription_status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">{{ __('—') }}</option>
                            @foreach (['active', 'trialing', 'past_due', 'canceled', 'unpaid', 'incomplete', 'incomplete_expired', 'paused'] as $st)
                                <option value="{{ $st }}" @selected(old('stripe_subscription_status') === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('stripe_subscription_status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">stripe_current_price_id</label>
                        <input name="stripe_current_price_id" value="{{ old('stripe_current_price_id') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="price_…" />
                        @error('stripe_current_price_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="stripe_subscription_cancel_at_period_end" value="1" class="rounded border-slate-300 text-violet-600" @checked(old('stripe_subscription_cancel_at_period_end')) />
                            {{ __('Cancelar renovação no fim do período') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar empresa e guardar') }}</button>
                <a href="{{ route('platform.assinaturas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>
