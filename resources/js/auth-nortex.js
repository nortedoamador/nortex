const PT_WEEKDAYS = [
    'DOMINGO',
    'SEGUNDA-FEIRA',
    'TERÇA-FEIRA',
    'QUARTA-FEIRA',
    'QUINTA-FEIRA',
    'SEXTA-FEIRA',
    'SÁBADO',
];

const NX_SPA_HEADERS = {
    'X-NX-SPA': '1',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
};

function isAuthPath(pathname) {
    return /(^|\/)(login|register|forgot-password)\/?$/.test(pathname.replace(/\/+$/, '') || '/');
}

function pad2(n) {
    return String(n).padStart(2, '0');
}

function readXsrfToken() {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
}

/**
 * Pós-logout: o primeiro GET pode reutilizar estado partido do SPA/bfcache.
 * `nx_rev=1` força um reload com URL limpa (equivalente a hard refresh).
 */
function nxAuthReloadAfterLogoutParam() {
    const p = new URLSearchParams(window.location.search);
    if (p.get('nx_rev') !== '1') {
        return false;
    }
    p.delete('nx_rev');
    const qs = p.toString();
    const clean = `${window.location.pathname}${qs ? `?${qs}` : ''}${window.location.hash}`;
    window.history.replaceState(null, '', clean);
    window.location.reload();
    return true;
}

function firstValidationError(errors) {
    if (!errors || typeof errors !== 'object') {
        return null;
    }
    for (const key of Object.keys(errors)) {
        const arr = errors[key];
        if (Array.isArray(arr) && arr[0]) {
            return arr[0];
        }
    }
    return null;
}

function tickClock(root) {
    if (!root) {
        return;
    }
    const d = new Date();
    const weekday = root.querySelector('[data-part="weekday"]');
    const timeEl = root.querySelector('[data-part="time"]');
    const dateEl = root.querySelector('[data-part="date"]');
    if (weekday) {
        weekday.textContent = PT_WEEKDAYS[d.getDay()];
    }
    if (timeEl) {
        timeEl.textContent = `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
    }
    if (dateEl) {
        dateEl.textContent = `${pad2(d.getDate())}/${pad2(d.getMonth() + 1)}/${d.getFullYear()}`;
    }
}

function initParallax(root) {
    if (!root || root.dataset.nxParallaxReady === '1') {
        return;
    }
    root.dataset.nxParallaxReady = '1';
    const url = root.dataset.bg;
    if (!url) {
        return;
    }
    root.querySelectorAll('.nx-auth-parallax-layer').forEach((layer) => {
        layer.style.backgroundImage = `url("${url}")`;
    });

    const layers = [...root.querySelectorAll('.nx-auth-parallax-layer[data-depth]')];

    const apply = (dx, dy) => {
        layers.forEach((layer) => {
            const depth = Number(layer.dataset.depth) || 20;
            const tx = dx * depth;
            const ty = dy * depth;
            const baseScale = layer.classList.contains('nx-auth-parallax-layer--back') ? 1.18 : 1.12;
            layer.style.transform = `translate3d(${tx}px, ${ty}px, 0) scale(${baseScale})`;
        });
    };

    const onMove = (clientX, clientY) => {
        const cx = window.innerWidth / 2;
        const cy = window.innerHeight / 2;
        const dx = (clientX - cx) / cx;
        const dy = (clientY - cy) / cy;
        apply(dx, dy);
    };

    window.addEventListener(
        'mousemove',
        (e) => {
            onMove(e.clientX, e.clientY);
        },
        { passive: true },
    );

    window.addEventListener(
        'deviceorientation',
        (e) => {
            if (e.gamma == null || e.beta == null) {
                return;
            }
            const dx = Math.max(-1, Math.min(1, e.gamma / 25));
            const dy = Math.max(-1, Math.min(1, (e.beta - 40) / 25));
            apply(dx, dy);
        },
        true,
    );
}

const REMEMBER_KEY = 'nortex_auth_remember_login';

function nxIsValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

function nxFirstCharUpper(s) {
    const t = (s || '').trim();
    if (!t) {
        return '•';
    }
    const first = t[0];
    return first.toUpperCase();
}

function initLoginForm() {
    const form = document.getElementById('nx-login-form');
    if (!form) {
        return;
    }

    const step1 = document.getElementById('nx-login-step1');
    const step2 = document.getElementById('nx-login-step2');
    const emailInput = document.getElementById('nx-login-email');
    const emailHidden = document.getElementById('nx-login-email-hidden');
    const brandingDefault = document.getElementById('nx-login-branding-default');
    const brandingUser = document.getElementById('nx-login-branding-user');
    const brandUsername = document.getElementById('nx-login-brand-username');
    const brandAvatarLetter = document.getElementById('nx-login-brand-avatar-letter');
    const passwordInput = document.getElementById('nx-login-password');
    const remember = document.getElementById('nx-login-remember');
    const clientAlert = document.getElementById('nx-login-client-alert');
    const btnStep1 = document.getElementById('nx-login-btn-step1');
    const btnChangeUser = document.getElementById('nx-login-change-user');
    const togglePw = document.getElementById('nx-login-toggle-password');

    if (form.dataset.nxLoginBound === '1') {
        return;
    }
    form.dataset.nxLoginBound = '1';

    const saved = localStorage.getItem(REMEMBER_KEY);
    if (saved && emailInput && !emailInput.value) {
        emailInput.value = saved;
    }

    const showClientError = (msg) => {
        if (!clientAlert) {
            return;
        }
        const text = clientAlert.querySelector('[data-alert-text]');
        if (text) {
            text.textContent = msg;
        }
        clientAlert.classList.remove('nx-auth-inline-alert--hidden');
        clientAlert.classList.add('nx-auth-inline-alert--animate');
        document.getElementById('nx-login-user-row')?.classList.add('nx-auth-row--inline-error');
    };

    const hideClientError = () => {
        clientAlert?.classList.add('nx-auth-inline-alert--hidden');
        clientAlert?.classList.remove('nx-auth-inline-alert--animate');
        document.getElementById('nx-login-user-row')?.classList.remove('nx-auth-row--inline-error');
    };

    const applyPasswordStepUi = (emailValue, displayName) => {
        const resolvedEmail = (emailValue || '').trim().toLowerCase();
        if (emailHidden) {
            emailHidden.value = resolvedEmail;
        }
        const label = (displayName || '').trim() || resolvedEmail;
        if (brandAvatarLetter) {
            brandAvatarLetter.textContent = nxFirstCharUpper(label);
        }
        if (brandUsername) {
            brandUsername.textContent = label;
        }
        brandingDefault?.setAttribute('hidden', '');
        brandingUser?.removeAttribute('hidden');
        step1?.setAttribute('hidden', '');
        step2?.removeAttribute('hidden');
        step2?.classList.remove('nx-auth-step-reveal');
        void step2?.offsetWidth;
        step2?.classList.add('nx-auth-step-reveal');
        if (passwordInput) {
            passwordInput.required = true;
        }
        passwordInput?.focus();
    };

    const tryAdvanceToPasswordStep = async () => {
        hideClientError();
        const v = (emailInput?.value ?? '').trim();
        if (!v) {
            showClientError('Digite seu e-mail');
            emailInput?.focus();
            return;
        }
        if (!nxIsValidEmail(v)) {
            showClientError('Digite um e-mail válido');
            emailInput?.focus();
            return;
        }

        const lookupUrl = form.dataset.authLookupUrl;
        if (!lookupUrl) {
            applyPasswordStepUi(v, null);
            return;
        }

        const token = form.querySelector('input[name="_token"]')?.value ?? '';
        btnStep1.disabled = true;
        try {
            const body = new FormData();
            body.append('email', v);
            body.append('_token', token);

            const res = await fetch(lookupUrl, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const ct = res.headers.get('content-type') || '';
            const json = ct.includes('application/json') ? await res.json().catch(() => ({})) : {};

            if (!res.ok) {
                const msg =
                    (typeof json.message === 'string' && json.message) ||
                    json.errors?.email?.[0] ||
                    'E-mail não cadastrado.';
                showClientError(msg);
                emailInput?.focus();
                return;
            }

            applyPasswordStepUi(json.email || v, json.name);
        } catch {
            showClientError('Não foi possível verificar o e-mail. Tente novamente.');
            emailInput?.focus();
        } finally {
            btnStep1.disabled = false;
        }
    };

    const goBackToUserStep = () => {
        hideClientError();
        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.required = false;
        }
        if (emailHidden && emailInput) {
            emailInput.value = emailHidden.value.trim();
        }
        brandingUser?.setAttribute('hidden', '');
        brandingDefault?.removeAttribute('hidden');
        step2?.setAttribute('hidden', '');
        step1?.removeAttribute('hidden');
        step2?.classList.remove('nx-auth-step-reveal');
        document.querySelectorAll('#nx-auth-surface .nx-auth-spa-flash').forEach((n) => n.remove());
        document.querySelectorAll('#nx-auth-surface [data-nx-alert]').forEach((n) => n.remove());
        emailInput?.focus();
    };

    btnStep1?.addEventListener('click', () => {
        void tryAdvanceToPasswordStep();
    });

    btnChangeUser?.addEventListener('click', goBackToUserStep);

    emailInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            void tryAdvanceToPasswordStep();
        }
    });

    form.addEventListener('submit', () => {
        if (emailHidden && emailInput) {
            emailHidden.value = emailInput.value.trim();
        }
        if (remember?.checked && emailInput) {
            localStorage.setItem(REMEMBER_KEY, emailInput.value.trim());
        } else {
            localStorage.removeItem(REMEMBER_KEY);
        }
    });

    togglePw?.addEventListener('click', () => {
        if (!passwordInput) {
            return;
        }
        const isText = passwordInput.type === 'text';
        passwordInput.type = isText ? 'password' : 'text';
        togglePw.setAttribute('aria-pressed', String(!isText));
    });

    if (step2 && !step2.hasAttribute('hidden')) {
        step1?.setAttribute('hidden', '');
        brandingDefault?.setAttribute('hidden', '');
        brandingUser?.removeAttribute('hidden');
        if (passwordInput) {
            passwordInput.required = true;
        }
    }
}

function rebindAuthPage() {
    document.getElementById('nx-auth-surface')?.querySelectorAll('form').forEach((el) => {
        delete el.dataset.nxFormBound;
    });
    const loginForm = document.getElementById('nx-login-form');
    if (loginForm) {
        delete loginForm.dataset.nxLoginBound;
    }
    initLoginForm();
}

function injectSpaAlert(surface, message, variant = 'error') {
    surface.querySelectorAll('.nx-auth-spa-flash').forEach((n) => n.remove());
    const wrap = document.createElement('div');
    wrap.className =
        variant === 'ok'
            ? 'nx-auth-alert nx-auth-alert--ok nx-auth-alert--animate nx-auth-spa-flash'
            : 'nx-auth-alert nx-auth-alert--animate nx-auth-spa-flash';
    wrap.setAttribute('role', 'alert');
    const svgErr =
        '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 7v7M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    const svgOk =
        '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M8 12l3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    wrap.innerHTML = `${variant === 'ok' ? svgOk : svgErr}<span></span>`;
    wrap.querySelector('span').textContent = message;
    surface.insertBefore(wrap, surface.firstChild);
}

function setSurfaceBusy(surface, busy) {
    surface.classList.toggle('nx-auth-surface--busy', busy);
    surface.querySelectorAll('form').forEach((f) => f.classList.toggle('nx-auth-form--busy', busy));
}

async function loadAuthPage(pathOrUrl, { push = true } = {}) {
    const surface = document.getElementById('nx-auth-surface');
    if (!surface) {
        return;
    }

    const url = pathOrUrl.startsWith('http') ? pathOrUrl : `${window.location.origin}${pathOrUrl}`;

    surface.classList.remove('nx-auth-surface--visible', 'nx-auth-surface--enter');
    surface.classList.add('nx-auth-surface--exit');

    await new Promise((r) => setTimeout(r, 220));

    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: { ...NX_SPA_HEADERS },
    });

    if (!res.ok) {
        surface.classList.remove('nx-auth-surface--exit');
        surface.classList.add('nx-auth-surface--visible');
        window.location.href = url;
        return;
    }

    const data = await res.json();
    surface.innerHTML = data.html;
    const appName = document.body.dataset.appName || 'NorteX';
    if (data.title) {
        document.title = `${data.title} — ${appName}`;
    }

    surface.classList.remove('nx-auth-surface--exit');
    surface.classList.add('nx-auth-surface--enter');
    requestAnimationFrame(() => {
        surface.classList.add('nx-auth-surface--visible');
    });

    if (push) {
        const path = new URL(url).pathname + new URL(url).search;
        history.pushState({ nxAuth: 1 }, '', path);
    }

    rebindAuthPage();
    bindAuthFormSubmits();
}

function bindSpaLinks() {
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[data-nx-spa-link]');
        if (!a || !a.getAttribute('href')) {
            return;
        }
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
            return;
        }
        if (a.target && a.target !== '_self') {
            return;
        }
        let u;
        try {
            u = new URL(a.href, window.location.origin);
        } catch {
            return;
        }
        if (u.origin !== window.location.origin) {
            return;
        }
        if (!isAuthPath(u.pathname)) {
            return;
        }
        e.preventDefault();
        loadAuthPage(u.pathname + u.search, { push: true });
    });
}

function bindAuthFormSubmits() {
    const surface = document.getElementById('nx-auth-surface');
    if (!surface) {
        return;
    }

    surface.querySelectorAll('form[action][method="post"], form[action][method="POST"]').forEach((form) => {
        if (form.dataset.nxFormBound === '1') {
            return;
        }
        form.dataset.nxFormBound = '1';

        form.addEventListener('submit', async (e) => {
            if (form.dataset.nxSkipSpa === '1') {
                return;
            }
            e.preventDefault();

            const action = form.getAttribute('action');
            if (!action) {
                return;
            }

            setSurfaceBusy(surface, true);

            const body = new FormData(form);
            const headers = {
                ...NX_SPA_HEADERS,
                'X-XSRF-TOKEN': readXsrfToken(),
                Accept: 'application/json',
            };

            let res;
            try {
                res = await fetch(action, {
                    method: 'POST',
                    body,
                    credentials: 'same-origin',
                    headers,
                });
            } catch {
                setSurfaceBusy(surface, false);
                injectSpaAlert(surface, 'Não foi possível conectar. Verifique sua rede.', 'error');
                return;
            }

            setSurfaceBusy(surface, false);

            const ct = res.headers.get('content-type') || '';
            const json = ct.includes('application/json') ? await res.json().catch(() => ({})) : null;

            if (res.status === 419) {
                injectSpaAlert(surface, 'Sessão expirada. Recarregando…', 'error');
                window.location.reload();
                return;
            }

            if (res.ok && json?.redirect) {
                window.location.assign(json.redirect);
                return;
            }

            if (res.ok && json?.refetch) {
                await loadAuthPage(`${window.location.pathname}${window.location.search}`, { push: false });
                return;
            }

            if (res.status === 422 && json) {
                const msg = json.message || firstValidationError(json.errors) || 'Verifique os dados e tente novamente.';
                injectSpaAlert(surface, typeof msg === 'string' ? msg : 'Dados inválidos.', 'error');
                return;
            }

            if (!res.ok) {
                injectSpaAlert(surface, 'Algo deu errado. Tente novamente.', 'error');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (nxAuthReloadAfterLogoutParam()) {
        return;
    }

    const surface = document.getElementById('nx-auth-surface');
    if (surface) {
        surface.classList.remove('nx-auth-surface--exit', 'nx-auth-surface--busy');
        surface.querySelectorAll('form').forEach((f) => {
            f.classList.remove('nx-auth-form--busy');
        });
        surface.classList.add('nx-auth-surface--visible');
    }

    const root = document.getElementById('nx-auth-parallax');
    initParallax(root);

    const clock = document.getElementById('nx-auth-clock');
    tickClock(clock);
    setInterval(() => tickClock(clock), 1000 * 15);

    initLoginForm();
    bindSpaLinks();
    bindAuthFormSubmits();

    window.addEventListener('popstate', () => {
        if (!isAuthPath(window.location.pathname)) {
            return;
        }
        loadAuthPage(`${window.location.pathname}${window.location.search}`, { push: false });
    });

    if (isAuthPath(window.location.pathname)) {
        history.replaceState({ nxAuth: 1 }, '', window.location.pathname + window.location.search);
    }
});
