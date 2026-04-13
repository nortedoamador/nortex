@if (session('status'))
    <div class="nx-auth-alert nx-auth-alert--ok nx-auth-alert--animate" role="status" data-nx-alert>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
            <path d="M8 12l3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

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
        <h1 class="nx-auth-page-title">Recuperar Senha</h1>
    </div>

    <p class="nx-auth-subtitle">Enviaremos um link para seu e-mail</p>

    <form method="POST" action="{{ route('password.email') }}" class="nx-auth-form-stack" data-nx-auth-form="forgot">
        @csrf

        <div class="nx-auth-field nx-auth-field--stagger" style="--nx-stagger: 0">
            <label class="sr-only" for="email_forgot">E-mail cadastrado</label>
            <input id="email_forgot" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="E-mail cadastrado">
        </div>

        <button type="submit" class="nx-auth-btn-primary nx-auth-btn-primary--block nx-auth-interactive nx-auth-field--stagger" style="--nx-stagger: 1" aria-label="Enviar link de recuperação">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </form>
</div>
