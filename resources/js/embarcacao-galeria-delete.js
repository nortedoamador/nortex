/**
 * Remoção de foto na galeria sem <form> aninhado (evita fechar o <form> pai no HTML).
 */
export function initEmbarcacaoGaleriaDelete() {
    if (typeof document === 'undefined') {
        return;
    }
    if (document.documentElement.dataset.nxEmbGaleriaDeleteBound === '1') {
        return;
    }
    document.documentElement.dataset.nxEmbGaleriaDeleteBound = '1';

    document.addEventListener(
        'click',
        async (e) => {
            const btn = e.target.closest('button[data-nx-embarcacao-foto-delete]');
            if (!(btn instanceof HTMLButtonElement)) {
                return;
            }
            e.preventDefault();
            const msg = btn.getAttribute('data-nx-confirm') || 'Remover?';
            if (!window.confirm(msg)) {
                return;
            }
            const url = btn.getAttribute('data-nx-url');
            const token = btn.getAttribute('data-nx-csrf');
            if (!url || !token) {
                return;
            }
            btn.disabled = true;
            try {
                const body = new URLSearchParams();
                body.set('_token', token);
                body.set('_method', 'DELETE');
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    redirect: 'follow',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html, application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body,
                });
                if (res.ok) {
                    window.location.reload();
                    return;
                }
                if (res.status === 419) {
                    window.alert('Sessão expirada. Atualize a página.');
                    return;
                }
                if (res.status === 403) {
                    window.alert('Sem permissão para remover.');
                    return;
                }
                window.alert('Não foi possível remover a foto.');
            } catch {
                window.alert('Não foi possível remover a foto.');
            } finally {
                btn.disabled = false;
            }
        },
        true,
    );
}
