/**
 * Máscara CNPJ em inputs com data-nx-mask="cnpj".
 */
function formatCnpjDisplay(digits) {
    const d = digits.replace(/\D/g, '').slice(0, 14);
    if (d.length <= 2) return d;
    if (d.length <= 5) return `${d.slice(0, 2)}.${d.slice(2)}`;
    if (d.length <= 8) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5)}`;
    if (d.length <= 12) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8)}`;
    return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8, 12)}-${d.slice(12)}`;
}

export function initCnpjMasks(root = document) {
    const bind = (input) => {
        if (input.dataset.nxCnpjBound === '1') return;
        input.dataset.nxCnpjBound = '1';

        if (input.value) {
            input.value = formatCnpjDisplay(input.value);
        }

        input.addEventListener('input', () => {
            const cur = input.selectionStart;
            const before = input.value;
            const formatted = formatCnpjDisplay(input.value);
            input.value = formatted;
            try {
                const diff = formatted.length - before.length;
                input.setSelectionRange(Math.max(0, (cur ?? 0) + diff), Math.max(0, (cur ?? 0) + diff));
            } catch {
                /* ignore */
            }
        });
    };

    root.querySelectorAll('input[data-nx-mask="cnpj"]').forEach(bind);

    if (root === document) {
        document.addEventListener('focusin', (e) => {
            const t = e.target;
            if (t && t.matches && t.matches('input[data-nx-mask="cnpj"]')) bind(t);
        });
    }
}
