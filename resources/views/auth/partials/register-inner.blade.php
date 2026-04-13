@if ($errors->any())
    <div class="nx-auth-alert nx-auth-alert--animate" role="alert" data-nx-alert>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
            <path d="M12 7v7M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<div class="nx-auth-panel nx-auth-panel--form">
    <div class="nx-auth-page-head">
        <a href="{{ route('login') }}" class="nx-auth-back nx-auth-interactive" aria-label="Voltar ao login" data-nx-spa-link>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
        <h1 class="nx-auth-page-title">Criar Conta</h1>
    </div>

    <form method="POST" action="{{ route('register') }}" class="nx-auth-form-stack" data-nx-auth-form="register">
        @csrf

        <div class="nx-auth-field nx-auth-field--stagger" style="--nx-stagger: 0">
            <label for="empresa_nome">Nome da empresa</label>
            <input id="empresa_nome" type="text" name="empresa_nome" value="{{ old('empresa_nome') }}" required autocomplete="organization" placeholder="Nome da empresa">
        </div>

        <div class="nx-auth-field nx-auth-field--stagger" style="--nx-stagger: 1">
            <label for="name">Nome</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Nome">
        </div>

        <div class="nx-auth-field nx-auth-field--stagger" style="--nx-stagger: 2">
            <label for="email">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="E-mail">
        </div>

        <div class="nx-auth-field nx-auth-field--stagger" style="--nx-stagger: 3">
            <label for="password">Senha</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Senha">
        </div>

        <div class="nx-auth-field nx-auth-field--stagger" style="--nx-stagger: 4">
            <label for="password_confirmation">Confirmar senha</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirmar senha">
        </div>

        <div class="nx-auth-form-footer">
            <a class="nx-auth-muted-link nx-auth-interactive" href="{{ route('login') }}" data-nx-spa-link>Já tem conta?</a>
            <button type="submit" class="nx-auth-btn-primary nx-auth-interactive">
                REGISTRAR
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </form>
</div>
