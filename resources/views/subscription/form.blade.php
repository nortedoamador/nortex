@extends('layouts.auth-nortex')

@section('title', 'Assinar')

@section('content')
    <div class="nx-auth-panel">
        <div class="nx-auth-branding-wrap">
            <div class="nx-auth-logo" aria-hidden="true"><span class="nx-auth-logo-mark">X</span></div>
            <div class="nx-auth-brand">Norte<span class="nx-accent">X</span></div>
        </div>

        <p class="nx-auth-muted" style="text-align:center;margin:0 0 1rem;font-size:0.95rem;">
            <strong>{{ $planLabel }}</strong> — R$ {{ $planPrice }} / mês
        </p>

        @if ($errors->any())
            <div class="nx-auth-alert nx-auth-alert--animate" role="alert" style="margin-bottom:1rem;">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('status'))
            <div class="nx-auth-alert" role="status" style="margin-bottom:1rem;">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form class="nx-auth-form-login" method="POST" action="{{ route('assinatura.store', ['plan' => $plan]) }}" autocomplete="on">
            @csrf
            <div class="nx-auth-row nx-auth-row--stacked" style="margin-bottom:0.65rem;">
                <label class="nx-auth-muted" style="font-size:0.75rem;display:block;margin-bottom:0.25rem;" for="nome_responsavel">Nome (responsável)</label>
                <input id="nome_responsavel" class="nx-auth-input" type="text" name="nome_responsavel" value="{{ old('nome_responsavel') }}" required maxlength="255" autocomplete="name" placeholder="Nome completo">
            </div>
            <div class="nx-auth-row nx-auth-row--stacked" style="margin-bottom:0.65rem;">
                <label class="nx-auth-muted" style="font-size:0.75rem;display:block;margin-bottom:0.25rem;" for="nome_empresa">Nome da empresa</label>
                <input id="nome_empresa" class="nx-auth-input" type="text" name="nome_empresa" value="{{ old('nome_empresa') }}" required maxlength="255" autocomplete="organization" placeholder="Razão social ou nome fantasia">
            </div>
            <div class="nx-auth-row nx-auth-row--stacked" style="margin-bottom:0.65rem;">
                <label class="nx-auth-muted" style="font-size:0.75rem;display:block;margin-bottom:0.25rem;" for="email">E-mail de contacto</label>
                <input id="email" class="nx-auth-input" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="seu@email.com">
            </div>
            <div class="nx-auth-row nx-auth-row--stacked" style="margin-bottom:1rem;">
                <label class="nx-auth-muted" style="font-size:0.75rem;display:block;margin-bottom:0.25rem;" for="telefone">Telefone</label>
                <input id="telefone" class="nx-auth-input" type="tel" name="telefone" value="{{ old('telefone') }}" required maxlength="40" autocomplete="tel" placeholder="(DDD) número">
            </div>

            <button type="submit" class="nx-auth-interactive" style="width:100%;padding:0.85rem;border-radius:0.75rem;font-weight:600;border:0;cursor:pointer;background:var(--nx-auth-blue);color:#fff;box-shadow:0 4px 16px rgba(0,174,239,0.35);">
                Continuar para pagamento seguro (Stripe)
            </button>
        </form>

        <p class="nx-auth-muted" style="text-align:center;margin:1rem 0 0;font-size:0.85rem;">
            <a class="nx-auth-link nx-auth-interactive" href="{{ route('assinatura.index') }}">« Outros planos</a>
            &nbsp;·&nbsp;
            <a class="nx-auth-link nx-auth-interactive" href="{{ route('login') }}">Entrar</a>
        </p>
    </div>
@endsection
