@extends('layouts.auth-nortex')

@section('title', __('Concluir assinatura'))

@section('content')
    <div class="nx-auth-panel">
        <div class="nx-auth-branding-wrap">
            <div class="nx-auth-logo" aria-hidden="true"><span class="nx-auth-logo-mark">X</span></div>
            <div class="nx-auth-brand">Norte<span class="nx-accent">X</span></div>
        </div>

        <h1 class="nx-auth-muted" style="text-align:center;margin:0 0 0.5rem;font-size:1.1rem;font-weight:600;color:inherit;">
            {{ __('Concluir assinatura') }}
        </h1>
        <p class="nx-auth-muted" style="text-align:center;margin:0 0 1rem;font-size:0.95rem;line-height:1.5;">
            {{ __('A organização :empresa está criada. Para aceder ao sistema é necessário pagar o plano Completo (R$ 497 / mês) no Stripe.', ['empresa' => $empresa->nome]) }}
        </p>

        @if (! $checkoutReady)
            <div class="nx-auth-alert nx-auth-alert--animate" role="alert" style="margin-bottom:1rem;">
                <span>{{ __('O checkout não está configurado (STRIPE_PRICE_FULL e chave Stripe). Contacte o suporte.') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="nx-auth-alert nx-auth-alert--animate" role="alert" style="margin-bottom:1rem;">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if ($checkoutReady)
            <form method="POST" action="{{ route('assinatura.pagamento-pendente.checkout') }}" class="nx-auth-form-login" style="margin-top:0.5rem;">
                @csrf
                <button type="submit" class="nx-auth-submit">{{ __('Ir para o pagamento (Stripe)') }}</button>
            </form>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="nx-auth-muted" style="text-align:center;margin:1.25rem 0 0;">
            @csrf
            <button type="submit" class="nx-auth-link nx-auth-interactive" style="background:none;border:none;cursor:pointer;padding:0;font:inherit;">{{ __('Sair') }}</button>
        </form>
    </div>
@endsection
