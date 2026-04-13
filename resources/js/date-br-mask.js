/**
 * Máscara global para datas em pt-BR: dd/mm/aaaa.
 * Use em inputs com atributo: data-nx-mask="date-br"
 */
function onlyDigits(str) {
    return (str || '').replace(/\D/g, '');
}

function formatDateBr(digits) {
    const d = digits.slice(0, 8);
    if (d.length <= 2) return d;
    if (d.length <= 4) return `${d.slice(0, 2)}/${d.slice(2)}`;
    return `${d.slice(0, 2)}/${d.slice(2, 4)}/${d.slice(4)}`;
}

function bindInputMask(input, formatFn) {
    if (!input || input.dataset.nxMaskReady === '1') return;
    input.dataset.nxMaskReady = '1';

    const apply = () => {
        const formatted = formatFn(input.value);
        if (formatted !== input.value) {
            input.value = formatted;
        }
    };

    input.addEventListener('input', apply);
    input.addEventListener('blur', apply);
    apply();
}

/**
 * @param {ParentNode} root
 */
export function initDateBrMasks(root = document) {
    if (!root) return;
    root.querySelectorAll('input[data-nx-mask="date-br"]').forEach((el) => {
        bindInputMask(el, (v) => formatDateBr(onlyDigits(v)));
    });
}

// Garante máscara em inputs adicionados dinamicamente (modais/AJAX).
if (typeof document !== 'undefined' && !document.documentElement.dataset.nxDateBrMaskAuto) {
    document.documentElement.dataset.nxDateBrMaskAuto = '1';
    document.addEventListener(
        'focusin',
        (e) => {
            const t = e.target;
            if (!(t instanceof HTMLInputElement)) return;
            if (t.matches('input[data-nx-mask="date-br"]')) {
                bindInputMask(t, (v) => formatDateBr(onlyDigits(v)));
            }
        },
        { capture: true },
    );
}

