<x-platform-layout :title="__('Assinaturas Stripe')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Assinaturas Stripe') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Lista de empresas, formulário manual no topo e ações Stripe (sincronizar, renovação, e-mail de senha).') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('platform.assinaturas.adicionar') }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-violet-600/25 transition hover:bg-violet-500">
                    {{ __('+ Adicionar empresa') }}
                </a>
                <a href="#cadastro-manual-stripe" class="inline-flex items-center rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-sm font-semibold text-violet-800 hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200 dark:hover:bg-violet-950">
                    {{ __('Editar assinatura existente') }}
                </a>
                <a href="{{ route('platform.empresas.index') }}" class="text-sm font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">
                    {{ __('← Empresas') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('platform.assinaturas.partials.manual-stripe-form', [
            'empresaEdicao' => $empresaEdicao,
            'empresaListaManual' => $empresaListaManual,
            'pickQ' => $pickQ,
            'q' => $q,
            'filtro' => $filtro,
        ])

        <form method="GET" action="{{ route('platform.assinaturas.index') }}" class="flex flex-wrap items-end gap-3">
            @if ($empresaEdicao)
                <input type="hidden" name="empresa_id" value="{{ $empresaEdicao->id }}" />
            @endif
            @if ($pickQ !== '')
                <input type="hidden" name="pick_q" value="{{ $pickQ }}" />
            @endif
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Nome, slug ou e-mail de contacto…') }}" class="mt-1 min-w-[220px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Filtro') }}</label>
                <select name="filtro" class="mt-1 rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="com_stripe" @selected($filtro === 'com_stripe')>{{ __('Com dados Stripe') }}</option>
                    <option value="sub_ativa" @selected($filtro === 'sub_ativa')>{{ __('Subscrição ativa / trial') }}</option>
                    <option value="todas" @selected($filtro === 'todas')>{{ __('Todas as empresas') }}</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Empresa') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Contacto') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Plano') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Estado Stripe') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Acesso até') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Stripe') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($empresas as $e)
                            @php
                                $dash = $e->stripeDashboardBaseUrl();
                                $cus = $e->stripe_customer_id;
                                $sub = $e->stripe_subscription_id;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('platform.empresas.show', $e) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ $e->nome }}</a>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $e->slug }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $e->email_contato ?: '—' }}
                                    @if ($e->telefone)
                                        <div class="text-xs text-slate-500">{{ $e->telefone }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $e->stripePlanLabel() ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($e->stripe_subscription_status)
                                        <span class="tabular-nums">{{ $e->stripe_subscription_status }}</span>
                                        @if ($e->stripe_subscription_cancel_at_period_end)
                                            <div class="mt-0.5 text-xs font-medium text-amber-600 dark:text-amber-400">{{ __('Cancela no fim do período') }}</div>
                                        @endif
                                    @elseif ($e->stripe_customer_id)
                                        <span class="text-slate-500">{{ __('Cliente sem subscrição') }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    @if ($e->acesso_plataforma_ate)
                                        <span class="tabular-nums">{{ $e->acesso_plataforma_ate->format('d/m/Y') }}</span>
                                        @if (! $e->acessoPlataformaVigente())
                                            <div class="mt-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">{{ __('Expirado') }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-400">{{ __('Sem limite') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                                    @if ($cus)
                                        <a href="{{ $dash }}/customers/{{ $cus }}" target="_blank" rel="noopener noreferrer" class="text-violet-600 hover:underline dark:text-violet-400">{{ __('Cliente') }}</a>
                                    @endif
                                    @if ($sub)
                                        <div class="mt-1">
                                            <a href="{{ $dash }}/subscriptions/{{ $sub }}" target="_blank" rel="noopener noreferrer" class="text-violet-600 hover:underline dark:text-violet-400">{{ __('Subscrição') }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <div class="flex flex-col items-end gap-2">
                                        <a href="{{ route('platform.assinaturas.index', array_filter(['q' => $q, 'filtro' => $filtro, 'pick_q' => $pickQ, 'empresa_id' => $e->id])) }}#cadastro-manual-stripe" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Editar IDs') }}</a>
                                        <form method="POST" action="{{ route('platform.assinaturas.sync', $e) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">{{ __('Sincronizar') }}</button>
                                        </form>
                                        @if ($sub && in_array($e->stripe_subscription_status, ['active', 'trialing', 'past_due'], true))
                                            @if (! $e->stripe_subscription_cancel_at_period_end)
                                                <form method="POST" action="{{ route('platform.assinaturas.cancelar-fim-periodo', $e) }}" class="inline" onsubmit="return confirm(@js(__('A subscrição deixará de renovar no fim do período atual. Continuar?')));">
                                                    @csrf
                                                    <button type="submit" class="font-medium text-amber-700 hover:text-amber-900 dark:text-amber-400">{{ __('Não renovar') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('platform.assinaturas.manter-renovacao', $e) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="font-medium text-emerald-700 hover:text-emerald-900 dark:text-emerald-400">{{ __('Manter renovação') }}</button>
                                                </form>
                                            @endif
                                        @endif
                                        <form method="POST" action="{{ route('platform.assinaturas.reenviar-senha', $e) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('E-mail definir senha') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhuma empresa encontrada.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $empresas->links() }}</div>
    </div>
</x-platform-layout>
