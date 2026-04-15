{{--
  $empresaEdicao, $empresaListaManual, $pickQ, $q, $filtro
--}}
<div id="cadastro-manual-stripe" class="scroll-mt-4 rounded-2xl border border-violet-200/80 bg-violet-50/40 p-6 shadow-sm dark:border-violet-900/40 dark:bg-violet-950/20">
    <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Inserir ou gerir dados Stripe (manual)') }}</h3>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
        {{ __('Escolha a empresa, carregue os dados e edite os campos abaixo. Isto grava na NorteX (não altera o Stripe).') }}
    </p>

    <form method="GET" action="{{ route('platform.assinaturas.index') }}" class="mt-4 flex flex-wrap items-end gap-3 border-b border-violet-200/60 pb-5 dark:border-violet-900/40">
        <input type="hidden" name="q" value="{{ $q }}" />
        <input type="hidden" name="filtro" value="{{ $filtro }}" />
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Pesquisar empresa') }}</label>
            <input type="search" name="pick_q" value="{{ $pickQ }}" class="mt-1 min-w-[200px] rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="{{ __('Nome, slug ou e-mail…') }}" />
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Empresa') }}</label>
            <select name="empresa_id" required class="mt-1 min-w-[280px] max-w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="" disabled @selected(! $empresaEdicao)>{{ __('— Selecione uma empresa —') }}</option>
                @foreach ($empresaListaManual as $opt)
                    <option value="{{ $opt->id }}" @selected($empresaEdicao && (int) $empresaEdicao->id === (int) $opt->id)>
                        {{ $opt->nome }} @if ($opt->email_contato) — {{ $opt->email_contato }} @endif
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Carregar para editar') }}</button>
    </form>

    @if ($empresaEdicao)
        <form method="POST" action="{{ route('platform.assinaturas.manual.store') }}" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="empresa_id" value="{{ $empresaEdicao->id }}" />
            <input type="hidden" name="return_q" value="{{ $q }}" />
            <input type="hidden" name="return_filtro" value="{{ $filtro }}" />
            <input type="hidden" name="return_pick_q" value="{{ $pickQ }}" />

            <p class="text-sm text-slate-700 dark:text-slate-300">
                <strong>{{ $empresaEdicao->nome }}</strong>
                <span class="text-slate-500">({{ $empresaEdicao->slug }})</span>
            </p>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400" for="manual_acesso_plataforma_ate">{{ __('Acesso à plataforma até') }}</label>
                <input id="manual_acesso_plataforma_ate" type="date" name="acesso_plataforma_ate" value="{{ old('acesso_plataforma_ate', $empresaEdicao->acesso_plataforma_ate?->format('Y-m-d')) }}" class="mt-1 max-w-xs rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Vazio = sem limite. No fim do dia selecionado ainda há acesso; no dia seguinte o login tenant é bloqueado.') }}</p>
                @error('acesso_plataforma_ate')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400" for="manual_stripe_customer_id">stripe_customer_id</label>
                    <input id="manual_stripe_customer_id" name="stripe_customer_id" type="text" value="{{ old('stripe_customer_id', $empresaEdicao->stripe_customer_id) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="cus_…" autocomplete="off" />
                    @error('stripe_customer_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400" for="manual_stripe_subscription_id">stripe_subscription_id</label>
                    <input id="manual_stripe_subscription_id" name="stripe_subscription_id" type="text" value="{{ old('stripe_subscription_id', $empresaEdicao->stripe_subscription_id) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="sub_…" autocomplete="off" />
                    @error('stripe_subscription_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400" for="manual_stripe_subscription_status">{{ __('Estado da subscrição') }}</label>
                    <select id="manual_stripe_subscription_status" name="stripe_subscription_status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">{{ __('— Vazio —') }}</option>
                        @foreach (['active', 'trialing', 'past_due', 'canceled', 'unpaid', 'incomplete', 'incomplete_expired', 'paused'] as $st)
                            <option value="{{ $st }}" @selected(old('stripe_subscription_status', $empresaEdicao->stripe_subscription_status) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    @error('stripe_subscription_status')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400" for="manual_stripe_current_price_id">stripe_current_price_id</label>
                    <input id="manual_stripe_current_price_id" name="stripe_current_price_id" type="text" value="{{ old('stripe_current_price_id', $empresaEdicao->stripe_current_price_id) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="price_…" autocomplete="off" />
                    @error('stripe_current_price_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="stripe_subscription_cancel_at_period_end" value="1" class="rounded border-slate-300 text-violet-600" @checked((bool) old('stripe_subscription_cancel_at_period_end', $empresaEdicao->stripe_subscription_cancel_at_period_end)) />
                <span>{{ __('Cancelar renovação no fim do período (cancel_at_period_end)') }}</span>
            </label>

            @error('empresa_id')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

            <div class="flex flex-wrap gap-3 pt-1">
                <button type="submit" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-violet-500">{{ __('Guardar dados Stripe') }}</button>
                <a href="{{ route('platform.assinaturas.index', ['q' => $q, 'filtro' => $filtro, 'pick_q' => $pickQ]) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Limpar seleção') }}</a>
            </div>
        </form>
    @else
        <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">{{ __('Selecione uma empresa na lista e clique em «Carregar para editar» para mostrar os campos.') }}</p>
    @endif
</div>
