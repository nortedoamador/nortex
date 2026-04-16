@extends('layouts.auth-nortex')

@section('title', __('Manutenção'))

@section('content')
    <div class="nx-auth-panel nx-auth-panel--form">
        <div class="nx-auth-branding-wrap">
            <div class="nx-auth-branding-state">
                <div class="nx-auth-logo" aria-hidden="true"><span class="nx-auth-logo-mark">!</span></div>
                <div class="nx-auth-brand">{{ __('Manutenção') }}</div>
            </div>
        </div>

        <div class="mt-8 w-full max-w-[420px] space-y-3 text-center text-sm text-white/85">
            <p>{{ __('A plataforma está temporariamente indisponível para manutenção (atualizações, reparos ou melhorias).') }}</p>
            <p>{{ __('Volte a tentar dentro de alguns minutos. Agradecemos a sua compreensão.') }}</p>
        </div>

        <div class="mt-8 w-full max-w-[420px]">
            <a href="{{ route('login') }}" class="nx-auth-btn-primary nx-auth-btn-primary--block nx-auth-interactive text-center">
                {{ __('Ir para o início de sessão') }}
            </a>
        </div>
    </div>
@endsection
