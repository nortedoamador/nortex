@php
    $showPasswordStep = filled(old('email')) && $errors->any();
    $serverMessage = $errors->first('email') ?: $errors->first('password');
    $loginDisplayName = null;
    if ($showPasswordStep && filled(old('email'))) {
        $loginDisplayName = \App\Models\User::query()
            ->whereRaw('LOWER(email) = ?', [\Illuminate\Support\Str::lower(old('email'))])
            ->value('name');
    }
    $loginAvatarSource = $loginDisplayName ?: old('email');
@endphp

@if ($serverMessage)
    <div class="nx-auth-alert nx-auth-alert--animate" role="alert" data-nx-alert>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
            <path d="M12 7v7M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span>{{ $serverMessage }}</span>
    </div>
@endif

<div class="nx-auth-panel">
    <div class="nx-auth-branding-wrap">
        <div id="nx-login-branding-default" class="nx-auth-branding-state" @if($showPasswordStep) hidden @endif>
            <div class="nx-auth-logo" aria-hidden="true"><span class="nx-auth-logo-mark">X</span></div>
            <div class="nx-auth-brand">Norte<span class="nx-accent">X</span></div>
        </div>
        <div id="nx-login-branding-user" class="nx-auth-branding-state" @if(!$showPasswordStep) hidden @endif>
            <div class="nx-auth-logo nx-auth-logo--user" aria-hidden="true">
                <span id="nx-login-brand-avatar-letter">{{ $showPasswordStep && filled($loginAvatarSource) ? Str::upper(Str::substr($loginAvatarSource, 0, 1)) : '•' }}</span>
            </div>
            <div class="nx-auth-brand nx-auth-brand--user" id="nx-login-brand-username">{{ $showPasswordStep ? ($loginDisplayName ?? old('email')) : '' }}</div>
        </div>
    </div>

    <form id="nx-login-form" class="nx-auth-form-login" method="POST" action="{{ route('login') }}" autocomplete="on" data-nx-auth-form="login" data-auth-lookup-url="{{ route('auth.lookup-email') }}">
        @csrf
        <input type="hidden" name="email" id="nx-login-email-hidden" value="{{ old('email') }}">

        <div class="nx-auth-step nx-auth-step--1" id="nx-login-step1" @if($showPasswordStep) hidden @endif>
            <div class="nx-auth-row nx-auth-row--stacked nx-auth-interactive" id="nx-login-user-row">
                <div class="nx-auth-row__line">
                    <input
                        id="nx-login-email"
                        class="nx-auth-input"
                        type="email"
                        inputmode="email"
                        autocomplete="username"
                        placeholder="E-mail"
                        value="{{ old('email') }}"
                    >
                    <div class="nx-auth-row__suffix">
                        <button type="button" class="nx-auth-submit-sq" id="nx-login-btn-step1" aria-label="Continuar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="nx-auth-inline-alert nx-auth-inline-alert--hidden" id="nx-login-client-alert" role="alert" aria-live="polite" data-nx-client-alert>
                    <svg class="nx-auth-inline-alert__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                        <path d="M12 8v5M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span data-alert-text>Digite seu e-mail</span>
                </div>
            </div>

            <label class="nx-auth-remember">
                <input type="checkbox" name="remember" id="nx-login-remember" value="1" @checked(old('remember'))>
                <span class="nx-auth-check" aria-hidden="true">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="label">Lembrar e-mail</span>
            </label>

            <a class="nx-auth-link nx-auth-link-center nx-auth-interactive" href="{{ route('register') }}" data-nx-spa-link>Criar conta</a>
        </div>

        <div class="nx-auth-step nx-auth-step--2" id="nx-login-step2" @if(!$showPasswordStep) hidden @endif>
            <div class="nx-auth-row nx-auth-interactive">
                <input
                    id="nx-login-password"
                    class="nx-auth-input"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Senha"
                >
                <div class="nx-auth-row__suffix">
                    <button type="button" class="nx-auth-icon-btn" id="nx-login-toggle-password" aria-label="Mostrar senha">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2" />
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </button>
                    <button type="submit" class="nx-auth-submit-sq" id="nx-login-btn-submit" aria-label="Entrar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="nx-auth-step2-links">
                <button type="button" class="nx-auth-link nx-auth-link-center nx-auth-interactive nx-auth-link--btn" id="nx-login-change-user">
                    Trocar e-mail
                </button>
                <a class="nx-auth-link nx-auth-link-center nx-auth-interactive" href="{{ route('password.request') }}" data-nx-spa-link>Esqueci minha senha</a>
            </div>
        </div>
    </form>
</div>
