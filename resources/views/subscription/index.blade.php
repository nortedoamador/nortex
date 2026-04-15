@extends('layouts.auth-nortex')

@section('title', 'Planos')

@section('content')
    <div class="nx-auth-panel">
        <div class="nx-auth-branding-wrap">
            <div class="nx-auth-logo" aria-hidden="true"><span class="nx-auth-logo-mark">X</span></div>
            <div class="nx-auth-brand">Norte<span class="nx-accent">X</span></div>
        </div>
        <p class="nx-auth-muted" style="text-align:center;margin:0 0 1.25rem;font-size:0.95rem;line-height:1.45;">
            Escolha o plano. Após o pagamento receberá um e-mail para definir a senha e entrar na plataforma.
        </p>

        @if (! $basicReady || ! $fullReady)
            <div class="nx-auth-alert" role="alert" style="margin-bottom:1rem;">
                <span>Os planos não estão configurados no servidor (Stripe). Contacte o administrador.</span>
            </div>
        @endif

        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            <a
                href="{{ route('assinatura.create', ['plan' => 'basica']) }}"
                class="nx-auth-interactive"
                style="display:block;text-align:center;text-decoration:none;padding:0.85rem 1rem;border-radius:0.75rem;font-weight:600;background:var(--nx-auth-blue);color:#fff;box-shadow:0 4px 16px rgba(0,174,239,0.35);{{ $basicReady ? '' : 'opacity:0.5;pointer-events:none;' }}"
            >
                Essencial — R$ 297 / mês <span style="display:block;font-size:0.8rem;font-weight:500;opacity:0.9;">Sem módulo financeiro</span>
            </a>
            <a
                href="{{ route('assinatura.create', ['plan' => 'completa']) }}"
                class="nx-auth-interactive"
                style="display:block;text-align:center;text-decoration:none;padding:0.85rem 1rem;border-radius:0.75rem;font-weight:600;background:var(--nx-auth-blue);color:#fff;box-shadow:0 4px 16px rgba(0,174,239,0.35);{{ $fullReady ? '' : 'opacity:0.5;pointer-events:none;' }}"
            >
                Completo — R$ 497 / mês <span style="display:block;font-size:0.8rem;font-weight:500;opacity:0.9;">Inclui financeiro</span>
            </a>
        </div>

        <p class="nx-auth-muted" style="text-align:center;margin:1.25rem 0 0;font-size:0.85rem;">
            <a class="nx-auth-link nx-auth-interactive" href="{{ route('login') }}">Já tenho conta — entrar</a>
        </p>
    </div>
@endsection
