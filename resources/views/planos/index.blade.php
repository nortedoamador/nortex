<x-app-layout :title="__('Planos')">
    @php
        $recursosEssencial = [
            __('Dashboard completo'),
            __('Processos automatizados'),
            __('Geração automática de documentos'),
            __('Documentação conforme NORMAM (201, 202, 211 e 212)'),
            __('Cadastro e histórico de clientes'),
            __('Centralização de processos'),
            __('Acesso via desktop'),
        ];
        $recursosProfissional = [
            __('Dashboard completo'),
            __('Processos automatizados'),
            __('Geração automática de documentos'),
            __('Documentação conforme NORMAM (201, 202, 211 e 212)'),
            __('Cadastro e histórico de clientes'),
            __('Centralização de processos'),
            __('Acesso via desktop e mobile'),
            __('Gestão financeira da operação'),
            __('Colaboradores ilimitados'),
            __('Controle de prazos e pendências'),
            __('Suporte prioritário'),
            __('Acesso prioritário a betas features'),
            __('Acesso antecipado a novas funcionalidades'),
        ];
    @endphp

    <div class="w-full min-h-[calc(100vh-3.5rem)] bg-[#F9F9F9] px-4 py-10 text-slate-900 sm:px-6 lg:min-h-screen lg:px-8">
        <div class="mx-auto max-w-5xl space-y-10">
            <div class="space-y-3 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    {{ __('Escolha seu plano e') }}
                    <span class="bg-gradient-to-r from-violet-600 to-fuchsia-600 bg-clip-text text-transparent">{{ __('comece agora') }}</span>
                </h1>
                <p class="mx-auto max-w-xl text-sm text-slate-600 sm:text-base">
                    {{ __('Organize e escale sua operação marítima. Cresça no seu ritmo.') }}
                </p>
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($planoAtivo)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                    <p class="text-sm font-medium text-emerald-900">{{ __('A sua organização já tem um plano ativo.') }}</p>
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-flex text-sm font-semibold text-violet-600 hover:text-violet-700">{{ __('Ir para o dashboard') }}</a>
                </div>
            @else
                <div class="grid gap-8 md:grid-cols-2 md:items-start">
                    <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Essencial') }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            {{ __('Para quem quer sair do papel e ter controle básico dos processos.') }}
                        </p>
                        <p class="mt-6 text-4xl font-bold tabular-nums text-slate-900">
                            R$ {{ number_format($displayBasicaBrl, 0, ',', '.') }}<span class="text-lg font-medium text-slate-500">/{{ __('mês') }}</span>
                        </p>

                        @if (! $checkoutBasicaReady)
                            <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                {{ __('O pagamento deste plano não está configurado (STRIPE_PRICE_BASIC e chave Stripe). Contacte o suporte.') }}
                            </p>
                        @else
                            <form method="POST" action="{{ route('planos.checkout') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="plan" value="basica">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#F9F9F9]"
                                >
                                    {{ __('Assinar Agora') }}
                                </button>
                            </form>
                        @endif

                        <p class="mt-8 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recursos') }}</p>
                        <ul class="mt-4 flex flex-1 flex-col gap-3 text-sm text-slate-700">
                            @foreach ($recursosEssencial as $item)
                                <li class="flex gap-3">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600" aria-hidden="true">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8 flex gap-3 border-t border-slate-200 pt-6">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 ring-2 ring-emerald-400/80 text-emerald-700" aria-hidden="true">
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <p class="text-xs leading-relaxed text-slate-900">
                                {{ __('Dados criptografados e protegidos com segurança de nível bancário') }}
                            </p>
                        </div>
                        <p class="mt-4 text-center text-xs text-slate-500">
                            {{ __('Ideal para começar e reduzir erros operacionais') }}
                        </p>
                    </div>

                    <div class="flex h-full flex-col rounded-2xl border-2 border-violet-600 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Profissional') }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            {{ __('Para quem quer produtividade, controle total e crescimento.') }}
                        </p>
                        <p class="mt-6 text-4xl font-bold tabular-nums text-slate-900">
                            R$ {{ number_format($displayCompletaBrl, 0, ',', '.') }}<span class="text-lg font-medium text-slate-500">/{{ __('mês') }}</span>
                        </p>

                        @if (! $checkoutCompletaReady)
                            <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                {{ __('O pagamento deste plano não está configurado (STRIPE_PRICE_FULL e chave Stripe). Contacte o suporte.') }}
                            </p>
                        @else
                            <form method="POST" action="{{ route('planos.checkout') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="plan" value="completa">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#F9F9F9]"
                                >
                                    {{ __('Assinar Agora') }}
                                </button>
                            </form>
                        @endif

                        <p class="mt-8 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recursos') }}</p>
                        <ul class="mt-4 flex flex-1 flex-col gap-3 text-sm text-slate-700">
                            @foreach ($recursosProfissional as $item)
                                <li class="flex gap-3">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600" aria-hidden="true">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8 flex gap-3 border-t border-slate-200 pt-6">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 ring-2 ring-emerald-400/80 text-emerald-700" aria-hidden="true">
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <p class="text-xs leading-relaxed text-slate-900">
                                {{ __('Dados criptografados e protegidos com segurança de nível bancário') }}
                            </p>
                        </div>
                        <p class="mt-4 text-center text-xs text-slate-500">
                            {{ __('Para escalar com produtividade e controle total') }}
                        </p>
                    </div>
                </div>

                <p class="text-center text-xs text-slate-500">
                    {{ __('Sem taxa de setup • Cancele quando quiser • Pagamento seguro') }}
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
