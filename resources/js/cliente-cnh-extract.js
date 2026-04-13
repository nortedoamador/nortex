/**
 * Leitura automática da CNH após seleção de imagem no cadastro de cliente.
 */
import { setDocumentoIdentidadeTipo } from './cliente-ficha-doc';

/**
 * @param {File} file
 */
function isFileReadableAsCnh(file) {
    const t = (file.type || '').toLowerCase();
    if (t.startsWith('image/')) {
        return true;
    }
    if (t === 'application/pdf') {
        return true;
    }
    const n = (file.name || '').toLowerCase();

    return n.endsWith('.pdf');
}

/**
 * @param {HTMLFormElement} form
 */
export function initClienteCnhExtract(form) {
    if (!form || form.dataset.nxClienteCnhExtractReady === '1') {
        return;
    }
    form.dataset.nxClienteCnhExtractReady = '1';

    const root = form.querySelector('[data-cnhc-extract-root="1"]');
    const statusEl = form.querySelector('[data-cnhc-status="1"]');
    if (!root) {
        return;
    }

    // getAttribute é fiável; dataset.cnhcExtractUrl a partir de data-cnhc-extract-url varia entre motores.
    const url = (root.getAttribute('data-cnhc-extract-url') || '').trim();
    const msgReading = root.getAttribute('data-cnhc-msg-reading') || 'Lendo CNH...';
    const msgFail =
        root.getAttribute('data-cnhc-msg-fail') ||
        'Não foi possível ler automaticamente. Preencha manualmente.';
    const msgReplace =
        root.getAttribute('data-cnhc-msg-replace') ||
        'Alguns campos já estão preenchidos. Substituir pelos dados lidos da CNH?';
    const msgNonReadable =
        root.getAttribute('data-cnhc-msg-nonreadable') ||
        'Este tipo de ficheiro não permite leitura automática da CNH. Será enviado ao salvar o cliente.';

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!url || !token) {
        return;
    }

    // Delegação no <form>: o input pode ser re-ligado pelo Alpine; listener no elemento evita referência stale.
    form.addEventListener('change', (ev) => {
        const t = ev.target;
        if (!(t instanceof HTMLInputElement) || t.type !== 'file') {
            return;
        }
        const nm = t.getAttribute('name') || '';
        if (!nm.startsWith('anexo_cnh')) {
            return;
        }

        void (async () => {
            const file = t.files?.[0];
            if (!file) {
                hideStatus(statusEl);
                return;
            }
            if (!isFileReadableAsCnh(file)) {
                showStatus(statusEl, msgNonReadable, 'text-slate-600 dark:text-slate-400');
                return;
            }

            showStatus(statusEl, msgReading, 'text-indigo-600 dark:text-indigo-400');

            const fd = new FormData();
            fd.append('file', file);

            let res;
            try {
                res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                    credentials: 'same-origin',
                });
            } catch {
                showStatus(statusEl, msgFail, 'text-amber-700 dark:text-amber-300');
                return;
            }

            let body = {};
            try {
                body = await res.json();
            } catch {
                body = {};
            }

            if (!res.ok || !body.ok || !body.data || typeof body.data !== 'object') {
                showStatus(statusEl, body.message || msgFail, 'text-amber-700 dark:text-amber-300');
                return;
            }

            hideStatus(statusEl);

            const replaceAll = shouldReplaceAll(form, body.data, msgReplace);
            applyExtractedData(form, body.data, replaceAll);

            setDocumentoIdentidadeTipo(form, 'cnh');
            const pf = form.querySelector('input[name="tipo_documento"][value="pf"]');
            if (pf) {
                pf.checked = true;
                pf.dispatchEvent(new Event('change', { bubbles: true }));
            }

            form.querySelector('#cpf')?.dispatchEvent(new Event('input', { bubbles: true }));
        })();
    });
}

/**
 * @param {HTMLFormElement} form
 * @param {Record<string, string|null|undefined>} data
 * @param {boolean} replaceAll
 */
function applyExtractedData(form, data, replaceAll) {
    const pairs = [
        ['nome', 'nome'],
        ['cpf', 'cpf'],
        ['data_nascimento', 'data_nascimento'],
        ['documento_identidade_numero', 'documento_identidade_numero'],
        ['orgao_emissor', 'orgao_emissor'],
        ['numero_cnh', 'numero_cnh'],
        ['categoria_cnh', 'categoria_cnh'],
        ['validade_cnh', 'validade_cnh'],
        ['validade', 'validade_cnh'],
        ['primeira_habilitacao', 'primeira_habilitacao'],
        ['naturalidade', 'naturalidade'],
        ['nome_pai', 'nome_pai'],
        ['nome_mae', 'nome_mae'],
    ];

    for (const [key, id] of pairs) {
        const v = data[key];
        if (v === null || v === undefined || String(v).trim() === '') {
            continue;
        }
        const val = String(v).trim();
        const el = form.querySelector(`#${id}`);
        if (!el) {
            continue;
        }
        if (!replaceAll && fieldHasMeaningfulValue(el)) {
            continue;
        }
        if (el.tagName === 'SELECT') {
            setSelectValue(el, val);
        } else {
            el.value = val;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}

/**
 * @param {HTMLElement} el
 */
function fieldHasMeaningfulValue(el) {
    if (el.tagName === 'SELECT') {
        return el.value !== '' && el.value != null;
    }
    return String(el.value || '').trim() !== '';
}

/**
 * @param {HTMLSelectElement} select
 * @param {string} value
 */
function setSelectValue(select, value) {
    const opts = [...select.options];
    const hit = opts.find((o) => o.value === value);
    if (hit) {
        select.value = value;
    } else {
        const o = document.createElement('option');
        o.value = value;
        o.textContent = value;
        o.selected = true;
        select.appendChild(o);
        select.value = value;
    }
    select.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * @param {HTMLFormElement} form
 * @param {Record<string, string|null|undefined>} data
 * @param {string} msgReplace
 */
function shouldReplaceAll(form, data, msgReplace) {
    const keys = [
        'nome',
        'cpf',
        'data_nascimento',
        'documento_identidade_numero',
        'orgao_emissor',
        'numero_cnh',
        'categoria_cnh',
        'validade_cnh',
        'validade',
        'primeira_habilitacao',
        'naturalidade',
        'nome_pai',
        'nome_mae',
    ];
    for (const key of keys) {
        const v = data[key];
        if (v === null || v === undefined || String(v).trim() === '') {
            continue;
        }
        const id = key;
        const el = form.querySelector(`#${id}`);
        if (el && fieldHasMeaningfulValue(el)) {
            return window.confirm(msgReplace);
        }
    }
    return true;
}

/**
 * @param {HTMLElement|null} el
 * @param {string} text
 * @param {string} cls
 */
function showStatus(el, text, cls) {
    if (!el) return;
    el.textContent = text;
    el.className = `mt-2 text-xs font-medium ${cls}`;
    el.classList.remove('hidden');
    el.setAttribute('aria-live', 'polite');
}

/**
 * @param {HTMLElement|null} el
 */
function hideStatus(el) {
    if (!el) return;
    el.textContent = '';
    el.classList.add('hidden');
}
