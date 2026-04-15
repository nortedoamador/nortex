@extends('layouts.auth-nortex')

@section('title', 'Pagamento')

@section('content')
    <div class="nx-auth-panel">
        <div class="nx-auth-branding-wrap">
            <div class="nx-auth-logo" aria-hidden="true"><span class="nx-auth-logo-mark">X</span></div>
            <div class="nx-auth-brand">Norte<span class="nx-accent">X</span></div>
        </div>

        @if ($paid)
            <p class="nx-auth-muted" style="text-align:center;margin:0 0 1rem;font-size:1rem;line-height:1.5;">
                Pagamento confirmado. Verifique o seu e-mail
                @if ($email)
                    (<strong>{{ $email }}</strong>)
                @endif
                — enviámos um link para <strong>definir a senha</strong>. Depois pode entrar em «Entrar» com esse e-mail.
            </p>
        @else
            <p class="nx-auth-muted" style="text-align:center;margin:0 0 1rem;font-size:1rem;line-height:1.5;">
                Obrigado. Se o pagamento foi concluído, receberá em breve o e-mail para definir a senha.
                Se não vir o e-mail, verifique a pasta de spam ou aguarde alguns minutos.
            </p>
        @endif

        <p class="nx-auth-muted" style="text-align:center;margin:0;font-size:0.9rem;">
            <a class="nx-auth-link nx-auth-interactive" href="{{ route('login') }}">Ir para o login</a>
        </p>
    </div>
@endsection
