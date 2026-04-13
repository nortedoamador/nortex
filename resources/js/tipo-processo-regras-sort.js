/**
 * Checklist de documentos (admin): arrastar linhas incluídas para definir ordem; hidden `ordem` atualizado no submit.
 */
export function initTipoProcessoRegrasSort(form) {
    const tbody = form.querySelector('[data-nx-checklist-tbody]');
    if (!tbody) {
        return;
    }

    let dragged = null;

    function ativoCheckbox(tr) {
        return tr.querySelector('input[type="checkbox"][name*="[ativo]"]');
    }

    function isIncluded(tr) {
        const cb = ativoCheckbox(tr);
        return !!(cb && cb.checked);
    }

    function refreshOrdem() {
        let i = 0;
        tbody.querySelectorAll('tr').forEach((tr) => {
            const inp = tr.querySelector('input.nx-linha-ordem');
            if (!inp) {
                return;
            }
            inp.value = isIncluded(tr) ? String(i++) : '0';
        });
    }

    function setRowDraggableState(tr) {
        const on = isIncluded(tr);
        tr.dataset.nxIncluded = on ? '1' : '0';
        const handle = tr.querySelector('[data-nx-drag-handle]');
        if (handle) {
            handle.draggable = on;
            handle.classList.toggle('opacity-40', !on);
            handle.classList.toggle('cursor-grab', on);
            handle.classList.toggle('pointer-events-none', !on);
            handle.setAttribute('aria-disabled', on ? 'false' : 'true');
        }
    }

    /** Incluídos primeiro (ordem atual), depois não incluídos por nome (ordem do DOM vinda do servidor). */
    function normalizeSections() {
        const rows = [...tbody.querySelectorAll('tr')];
        const inc = rows.filter(isIncluded);
        const exc = rows.filter((tr) => !isIncluded(tr));
        inc.forEach((tr) => tbody.appendChild(tr));
        exc.forEach((tr) => tbody.appendChild(tr));
        rows.forEach(setRowDraggableState);
        refreshOrdem();
    }

    tbody.querySelectorAll('tr').forEach(setRowDraggableState);
    refreshOrdem();

    tbody.addEventListener('change', (e) => {
        const t = e.target;
        if (t instanceof HTMLInputElement && t.matches('input[type="checkbox"][name*="[ativo]"]')) {
            const tr = t.closest('tr');
            if (tr) {
                setRowDraggableState(tr);
                normalizeSections();
            }
        }
    });

    tbody.addEventListener('dragstart', (e) => {
        const handle = e.target && e.target.closest ? e.target.closest('[data-nx-drag-handle]') : null;
        if (!handle || !handle.draggable) {
            e.preventDefault();
            return;
        }
        const tr = handle.closest('tr');
        if (!tr) {
            e.preventDefault();
            return;
        }
        dragged = tr;
        try {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', tr.dataset.docTipoId || '');
        } catch (_) {
            /* noop */
        }
        tr.classList.add('opacity-60', 'bg-slate-100', 'dark:bg-slate-800/80');
    });

    tbody.addEventListener('dragend', (e) => {
        const tr = e.target && e.target.closest ? e.target.closest('tr') : null;
        if (tr) {
            tr.classList.remove('opacity-60', 'bg-slate-100', 'dark:bg-slate-800/80');
        }
        dragged = null;
    });

    tbody.addEventListener('dragover', (e) => {
        if (!dragged || !isIncluded(dragged)) {
            return;
        }
        e.preventDefault();
        try {
            e.dataTransfer.dropEffect = 'move';
        } catch (_) {
            /* noop */
        }
        const tr = e.target && e.target.closest ? e.target.closest('tr') : null;
        if (!tr || tr === dragged || !isIncluded(tr)) {
            return;
        }
        const rect = tr.getBoundingClientRect();
        const mid = rect.top + rect.height / 2;
        if (e.clientY < mid) {
            tbody.insertBefore(dragged, tr);
        } else {
            tbody.insertBefore(dragged, tr.nextSibling);
        }
        refreshOrdem();
    });

    form.addEventListener('submit', () => {
        refreshOrdem();
    });
}
