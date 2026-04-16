/**
 * Checklist de documentos (admin / plataforma): arrastar linhas incluídas; hidden `ordem` no submit.
 * Modo duplo: `data-nx-checklist-tbody-included` + `data-nx-checklist-tbody-excluded` — move linhas ao marcar «Incluir».
 * Modo legado: um único `data-nx-checklist-tbody`.
 */
export function initTipoProcessoRegrasSort(form) {
    const tbodyIncluded = form.querySelector('[data-nx-checklist-tbody-included]');
    const tbodyExcluded = form.querySelector('[data-nx-checklist-tbody-excluded]');
    const tbodyLegacy = form.querySelector('[data-nx-checklist-tbody]');
    const dualMode = !!(tbodyIncluded && tbodyExcluded);
    const tbody = dualMode ? null : tbodyLegacy;

    if (!dualMode && !tbody) {
        return;
    }

    const dragTarget = dualMode ? tbodyIncluded : tbody;

    let dragged = null;

    function ativoCheckbox(tr) {
        return tr.querySelector('input[type="checkbox"][name*="[ativo]"]');
    }

    function isIncluded(tr) {
        const cb = ativoCheckbox(tr);
        return !!(cb && cb.checked);
    }

    function dataRowsIn(tbodyEl) {
        return tbodyEl ? [...tbodyEl.querySelectorAll('tr[data-doc-tipo-id]')] : [];
    }

    function allDataRows() {
        if (dualMode) {
            return [...dataRowsIn(tbodyIncluded), ...dataRowsIn(tbodyExcluded)];
        }
        return [...tbody.querySelectorAll('tr[data-doc-tipo-id]')];
    }

    function stripDataRows(tbodyEl) {
        if (!tbodyEl) {
            return;
        }
        tbodyEl.querySelectorAll('tr[data-doc-tipo-id]').forEach((tr) => tr.remove());
    }

    function refreshOrdem() {
        if (dualMode) {
            let i = 0;
            dataRowsIn(tbodyIncluded).forEach((tr) => {
                const inp = tr.querySelector('input.nx-linha-ordem');
                if (inp) {
                    inp.value = String(i++);
                }
            });
            dataRowsIn(tbodyExcluded).forEach((tr) => {
                const inp = tr.querySelector('input.nx-linha-ordem');
                if (inp) {
                    inp.value = '0';
                }
            });
            return;
        }

        let i = 0;
        tbody.querySelectorAll('tr[data-doc-tipo-id]').forEach((tr) => {
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

    function normalizeSections() {
        if (dualMode) {
            const inc = allDataRows().filter(isIncluded);
            const exc = allDataRows().filter((tr) => !isIncluded(tr));
            stripDataRows(tbodyIncluded);
            stripDataRows(tbodyExcluded);
            inc.forEach((tr) => tbodyIncluded.appendChild(tr));
            exc.forEach((tr) => tbodyExcluded.appendChild(tr));
            allDataRows().forEach(setRowDraggableState);
            refreshOrdem();
            return;
        }

        const rows = [...tbody.querySelectorAll('tr[data-doc-tipo-id]')];
        const inc = rows.filter(isIncluded);
        const exc = rows.filter((tr) => !isIncluded(tr));
        inc.forEach((tr) => tbody.appendChild(tr));
        exc.forEach((tr) => tbody.appendChild(tr));
        rows.forEach(setRowDraggableState);
        refreshOrdem();
    }

    const rowsInit = dualMode ? allDataRows() : [...tbody.querySelectorAll('tr[data-doc-tipo-id]')];
    rowsInit.forEach(setRowDraggableState);
    refreshOrdem();

    form.addEventListener('change', (e) => {
        const t = e.target;
        if (t instanceof HTMLInputElement && t.matches('input[type="checkbox"][name*="[ativo]"]')) {
            const tr = t.closest('tr[data-doc-tipo-id]');
            if (tr) {
                setRowDraggableState(tr);
                normalizeSections();
            }
        }
    });

    dragTarget.addEventListener('dragstart', (e) => {
        const handle = e.target && e.target.closest ? e.target.closest('[data-nx-drag-handle]') : null;
        if (!handle || !handle.draggable) {
            e.preventDefault();
            return;
        }
        const tr = handle.closest('tr[data-doc-tipo-id]');
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

    dragTarget.addEventListener('dragend', (e) => {
        const tr = e.target && e.target.closest ? e.target.closest('tr[data-doc-tipo-id]') : null;
        if (tr) {
            tr.classList.remove('opacity-60', 'bg-slate-100', 'dark:bg-slate-800/80');
        }
        dragged = null;
    });

    dragTarget.addEventListener('dragover', (e) => {
        if (!dragged || !isIncluded(dragged)) {
            return;
        }
        e.preventDefault();
        try {
            e.dataTransfer.dropEffect = 'move';
        } catch (_) {
            /* noop */
        }
        const tr = e.target && e.target.closest ? e.target.closest('tr[data-doc-tipo-id]') : null;
        if (!tr || tr === dragged || !isIncluded(tr)) {
            return;
        }
        const rect = tr.getBoundingClientRect();
        const mid = rect.top + rect.height / 2;
        if (e.clientY < mid) {
            dragTarget.insertBefore(dragged, tr);
        } else {
            dragTarget.insertBefore(dragged, tr.nextSibling);
        }
        refreshOrdem();
    });

    form.addEventListener('submit', () => {
        refreshOrdem();
    });
}
