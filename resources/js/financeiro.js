import Chart from 'chart.js/auto';

const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

function toCurrency(v) {
    const n = typeof v === 'number' ? v : Number(v || 0);
    return brl.format(Number.isFinite(n) ? n : 0);
}

function buildYearsOptions(selectEl, years) {
    if (!selectEl) return;
    selectEl.innerHTML = '';
    years.forEach((y) => {
        const opt = document.createElement('option');
        opt.value = String(y);
        opt.textContent = String(y);
        selectEl.appendChild(opt);
    });
}

function currentYearsList() {
    const now = new Date();
    const y = now.getFullYear();
    return [y, y - 1, y - 2, y - 3, y - 4];
}

export function initFinanceiroPage() {
    const root = document.getElementById('nx-financeiro');
    if (!root) return;

    const resumoUrl = root.dataset.resumoUrl;
    const caixaUrl = root.dataset.caixaUrl;
    const servicosUrl = root.dataset.servicosUrl;
    const listaUrl = root.dataset.listaUrl;
    const notasUrl = root.dataset.notasUrl;
    const storeAulasUrl = root.dataset.storeAulasUrl;
    const storeAdminUrl = root.dataset.storeAdminUrl;
    const storeDespesasUrl = root.dataset.storeDespesasUrl;
    const storeParceriasUrl = root.dataset.storeParceriasUrl;
    const storeEngenhariaUrl = root.dataset.storeEngenhariaUrl;
    const storeParceriaItemUrl = root.dataset.storeParceriaItemUrl;
    const storeEngenhariaItemUrl = root.dataset.storeEngenhariaItemUrl;
    const emitirNotaUrl = root.dataset.emitirNotaUrl;
    const updateAulasUrl = root.dataset.updateAulasUrl;
    const updateAdminUrl = root.dataset.updateAdminUrl;
    const updateDespesasUrl = root.dataset.updateDespesasUrl;
    const updateParceriasUrl = root.dataset.updateParceriasUrl;
    const updateParceriaItemUrl = root.dataset.updateParceriaItemUrl;
    const updateEngenhariaUrl = root.dataset.updateEngenhariaUrl;
    const updateEngenhariaItemUrl = root.dataset.updateEngenhariaItemUrl;
    const uploadAdminUrl = root.dataset.uploadAdminUrl;
    const uploadDespesasUrl = root.dataset.uploadDespesasUrl;
    const uploadParceriasUrl = root.dataset.uploadParceriasUrl;
    const uploadEngenhariaUrl = root.dataset.uploadEngenhariaUrl;
    const destroyAulasUrl = root.dataset.destroyAulasUrl;
    const destroyAdminUrl = root.dataset.destroyAdminUrl;
    const destroyDespesasUrl = root.dataset.destroyDespesasUrl;
    const destroyParceriasUrl = root.dataset.destroyParceriasUrl;
    const destroyParceriaItemUrl = root.dataset.destroyParceriaItemUrl;
    const destroyEngenhariaUrl = root.dataset.destroyEngenhariaUrl;
    const destroyEngenhariaItemUrl = root.dataset.destroyEngenhariaItemUrl;

    const anoEl = document.getElementById('nx-fin-ano');
    const mesEl = document.getElementById('nx-fin-mes');

    buildYearsOptions(anoEl, currentYearsList());

    const now = new Date();
    if (anoEl) anoEl.value = String(now.getFullYear());
    if (mesEl) mesEl.value = 'todos';

    let caixaChart = null;
    let servicosChart = null;
    let currentTab = 'overview';
    const listState = {
        aulas: { q: '', page: 1, limit: 25, sort: 'data_lancamento', dir: 'desc' },
        admin_direto: { q: '', page: 1, limit: 25, sort: 'data_servico', dir: 'desc' },
        despesas: { q: '', page: 1, limit: 25, sort: 'data_lancamento', dir: 'desc' },
        parcerias: { q: '', page: 1, limit: 10, sort: 'mes_referencia', dir: 'desc' },
        engenharia: { q: '', page: 1, limit: 10, sort: 'mes_referencia', dir: 'desc' },
        notas: { q: '', page: 1, limit: 25, status: 'false', sort: 'data', dir: 'desc' },
    };
    const debounceTimers = new Map();

    function setTab(tab) {
        currentTab = tab;
        document.querySelectorAll('[data-nx-fin-tab]').forEach((btn) => {
            const active = btn.dataset.nxFinTab === tab;
            btn.classList.toggle('bg-indigo-600', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('bg-white', !active);
            btn.classList.toggle('text-slate-700', !active);
        });
        document.querySelectorAll('[data-nx-fin-panel]').forEach((p) => {
            p.classList.toggle('hidden', p.dataset.nxFinPanel !== tab);
        });
    }

    async function loadResumo() {
        if (!resumoUrl) return;
        const ano = anoEl?.value || String(now.getFullYear());
        const mes = mesEl?.value || 'todos';
        const url = new URL(resumoUrl, window.location.origin);
        url.searchParams.set('ano', ano);
        url.searchParams.set('mes', mes);

        const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();

        setText('nx-aulas-qtd', String(data?.aulas?.qtd ?? 0));
        setText('nx-aulas-receita', toCurrency(data?.aulas?.receita ?? 0));
        setText('nx-aulas-lucro', toCurrency(data?.aulas?.lucro ?? 0));

        setText('nx-admin-qtd', String(data?.admin_direto?.qtd ?? 0));
        setText('nx-admin-receita', toCurrency(data?.admin_direto?.receita ?? 0));
        setText('nx-admin-lucro', toCurrency(data?.admin_direto?.lucro ?? 0));
        setText('nx-admin-aberto', toCurrency(data?.admin_direto?.aberto ?? 0));

        setText('nx-parcerias-qtd', String(data?.parcerias?.qtd ?? 0));
        setText('nx-parcerias-receita', toCurrency(data?.parcerias?.receita ?? 0));
        setText('nx-parcerias-lucro', toCurrency(data?.parcerias?.lucro ?? 0));

        setText('nx-engenharia-qtd', String(data?.engenharia?.qtd ?? 0));
        setText('nx-engenharia-receita', toCurrency(data?.engenharia?.receita ?? 0));
        setText('nx-engenharia-lucro', toCurrency(data?.engenharia?.lucro ?? 0));

        setText('nx-despesas-qtd', String(data?.despesas?.qtd ?? 0));
        setText('nx-despesas-total', toCurrency(data?.despesas?.total ?? 0));

        setText('nx-notas-pend-qtd', String(data?.notas?.pendentes_qtd ?? 0));
        setText('nx-notas-pend-val', toCurrency(data?.notas?.pendentes_valor ?? 0));
        setText('nx-notas-emit-qtd', String(data?.notas?.emitidas_qtd ?? 0));
        setText('nx-notas-emit-val', toCurrency(data?.notas?.emitidas_valor ?? 0));
    }

    async function loadCaixa() {
        if (!caixaUrl) return;
        const ano = anoEl?.value || String(now.getFullYear());
        const url = new URL(caixaUrl, window.location.origin);
        url.searchParams.set('ano', ano);

        const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();

        const canvas = document.getElementById('nx-chart-caixa');
        if (!canvas) return;

        if (caixaChart) caixaChart.destroy();

        const lucro = data?.lucro_liquido ?? [];
        const despesas = data?.despesas ?? [];
        const colors = lucro.map((v) => (Number(v) >= 0 ? 'rgba(22, 163, 74, 0.7)' : 'rgba(220, 38, 38, 0.7)'));
        const borders = lucro.map((v) => (Number(v) >= 0 ? '#16a34a' : '#dc2626'));

        caixaChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data?.labels ?? [],
                datasets: [
                    {
                        label: 'Lucro Líquido',
                        data: lucro,
                        backgroundColor: colors,
                        borderColor: borders,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Despesas',
                        data: despesas,
                        backgroundColor: 'rgba(220, 38, 38, 0.55)',
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (v) => toCurrency(v),
                        },
                    },
                },
            },
        });
    }

    async function loadServicos() {
        if (!servicosUrl) return;
        const ano = anoEl?.value || 'todos';
        const mes = mesEl?.value || 'todos';
        const url = new URL(servicosUrl, window.location.origin);
        url.searchParams.set('ano', ano);
        url.searchParams.set('mes', mes);

        const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();

        const canvas = document.getElementById('nx-chart-servicos');
        const empty = document.getElementById('nx-chart-servicos-empty');
        if (!canvas || !empty) return;

        const labels = data?.labels ?? [];
        const values = data?.values ?? [];

        if (labels.length === 0) {
            empty.classList.remove('hidden');
            canvas.classList.add('hidden');
            if (servicosChart) servicosChart.destroy();
            servicosChart = null;
            return;
        }

        empty.classList.add('hidden');
        canvas.classList.remove('hidden');

        if (servicosChart) servicosChart.destroy();

        servicosChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: [
                            '#4f46e5',
                            '#16a34a',
                            '#eab308',
                            '#dc2626',
                            '#8b5cf6',
                            '#06b6d4',
                            '#f97316',
                            '#ec4899',
                            '#14b8a6',
                            '#0f766e',
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 10, font: { size: 10 } },
                    },
                },
            },
        });
    }

    async function refreshAll() {
        await Promise.all([loadResumo(), loadCaixa(), loadServicos()]);
    }

    async function refreshActiveTab() {
        if (currentTab === 'aulas') await renderAulas();
        if (currentTab === 'admin_direto') await renderAdmin();
        if (currentTab === 'despesas') await renderDespesas();
        if (currentTab === 'parcerias') await renderParcerias();
        if (currentTab === 'engenharia') await renderEngenharia();
        if (currentTab === 'notas') await renderNotas();
    }

    anoEl?.addEventListener('change', async () => {
        await refreshAll();
        await refreshActiveTab();
    });
    mesEl?.addEventListener('change', async () => {
        await refreshAll();
        await refreshActiveTab();
    });

    refreshAll();

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta?.getAttribute('content') || '';
    }

    function showError(elId, message) {
        const el = document.getElementById(elId);
        if (!el) return;
        el.textContent = message || '';
        el.classList.toggle('hidden', !message);
    }

    async function loadLista(modulo) {
        if (!listaUrl) return;
        const ano = anoEl?.value || String(now.getFullYear());
        const mes = mesEl?.value || 'todos';
        const state = listState[modulo] || { q: '', page: 1, limit: 25 };
        const url = listaUrl.replace('__MODULO__', encodeURIComponent(modulo));
        const u = new URL(url, window.location.origin);
        u.searchParams.set('ano', ano);
        u.searchParams.set('mes', mes);
        u.searchParams.set('q', state.q || '');
        u.searchParams.set('page', String(state.page || 1));
        u.searchParams.set('limit', String(state.limit || 25));
        if (state.sort) u.searchParams.set('sort', String(state.sort));
        if (state.dir) u.searchParams.set('dir', String(state.dir));

        const res = await fetch(u.toString(), { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        return res.json();
    }

    function fmtDate(iso) {
        if (!iso) return '';
        const parts = String(iso).split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : String(iso);
    }

    function destroyUrl(tpl, id) {
        if (!tpl) return null;
        return tpl.replace('__ID__', encodeURIComponent(String(id)));
    }

    function routeWithId(tpl, id) {
        if (!tpl) return null;
        return tpl.replace('__ID__', encodeURIComponent(String(id)));
    }

    function setState(modulo, patch) {
        listState[modulo] = { ...(listState[modulo] || {}), ...patch };
    }

    function renderPagination(meta, containerId, modulo, rerender) {
        const container = document.getElementById(containerId);
        if (!container) return;
        if (!meta || Number(meta.total || 0) <= 0) {
            container.innerHTML = '';
            return;
        }
        const page = Number(meta.page || 1);
        const lastPage = Number(meta.last_page || 1);
        const from = meta.from ?? 0;
        const to = meta.to ?? 0;
        container.innerHTML = `
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Mostrando ${from}-${to} de ${meta.total}</p>
                <div class="flex items-center gap-2">
                    <button type="button" data-nx-page-prev="${modulo}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" ${page <= 1 ? 'disabled' : ''}>Anterior</button>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Página ${page} de ${lastPage}</span>
                    <button type="button" data-nx-page-next="${modulo}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" ${page >= lastPage ? 'disabled' : ''}>Próxima</button>
                </div>
            </div>
        `;
        container.querySelector('[data-nx-page-prev]')?.addEventListener('click', async () => {
            setState(modulo, { page: Math.max(1, page - 1) });
            await rerender();
        });
        container.querySelector('[data-nx-page-next]')?.addEventListener('click', async () => {
            setState(modulo, { page: Math.min(lastPage, page + 1) });
            await rerender();
        });
    }

    function bindFilterControls(modulo, renderFn, ids) {
        const searchEl = document.getElementById(ids.searchId);
        const limitEl = document.getElementById(ids.limitId);
        if (searchEl) {
            searchEl.value = listState[modulo]?.q || '';
            searchEl.addEventListener('input', () => {
                clearTimeout(debounceTimers.get(ids.searchId));
                const timer = window.setTimeout(async () => {
                    setState(modulo, { q: searchEl.value.trim(), page: 1 });
                    await renderFn();
                }, 250);
                debounceTimers.set(ids.searchId, timer);
            });
        }
        if (limitEl) {
            limitEl.value = String(listState[modulo]?.limit || 25);
            limitEl.addEventListener('change', async () => {
                setState(modulo, { limit: Number(limitEl.value || 25), page: 1 });
                await renderFn();
            });
        }
        const sortEl = ids.sortId ? document.getElementById(ids.sortId) : null;
        const dirEl = ids.dirId ? document.getElementById(ids.dirId) : null;
        if (sortEl) {
            const cur = listState[modulo]?.sort;
            if (cur && [...sortEl.options].some((o) => o.value === cur)) {
                sortEl.value = cur;
            }
            sortEl.addEventListener('change', async () => {
                setState(modulo, { sort: sortEl.value, page: 1 });
                await renderFn();
            });
        }
        if (dirEl) {
            dirEl.value = listState[modulo]?.dir === 'asc' ? 'asc' : 'desc';
            dirEl.addEventListener('change', async () => {
                setState(modulo, { dir: dirEl.value, page: 1 });
                await renderFn();
            });
        }
    }

    async function handleCreate(formEl, url, errorElId, method = 'POST') {
        if (!formEl || !url) return;
        showError(errorElId, '');
        const fd = new FormData(formEl);
        const intendedMethod = String(method || 'POST').toUpperCase();
        const requestMethod = intendedMethod === 'POST' ? 'POST' : 'POST';
        if (intendedMethod !== 'POST') {
            fd.set('_method', intendedMethod);
        }
        const res = await fetch(url, {
            method: requestMethod,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            body: fd,
        });

        if (!res.ok) {
            let msg = 'Não foi possível salvar.';
            try {
                const j = await res.json();
                if (j?.message) msg = j.message;
            } catch {
                // ignore
            }
            showError(errorElId, msg);
            return false;
        }

        formEl.reset();
        return true;
    }

    async function handleDelete(url) {
        if (!url) return false;
        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
        });
        return res.ok;
    }

    async function postFormData(url, formData) {
        if (!url) return false;
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            body: formData,
        });
        return res.ok;
    }

    function fillForm(formEl, values) {
        if (!formEl || !values) return;
        Object.entries(values).forEach(([name, value]) => {
            const field = formEl.elements.namedItem(name);
            if (!field) return;
            if (field instanceof RadioNodeList) return;
            if (field.type === 'checkbox') {
                field.checked = Boolean(value);
                return;
            }
            if (field.type === 'file') return;
            field.value = value ?? '';
        });
    }

    function setEditingState(formEl, options) {
        if (!formEl) return;
        formEl.dataset.editingId = String(options.id);
        formEl.dataset.submitMethod = 'PATCH';
        formEl.dataset.submitUrl = options.url;
        const label = document.getElementById(options.submitLabelId);
        if (label) label.textContent = options.editLabel;
        const cancel = document.getElementById(options.cancelId);
        if (cancel) cancel.classList.remove('hidden');
        if (options.onEnterEdit) options.onEnterEdit();
        formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetEditingState(formEl, options) {
        if (!formEl) return;
        formEl.reset();
        delete formEl.dataset.editingId;
        delete formEl.dataset.submitMethod;
        delete formEl.dataset.submitUrl;
        const label = document.getElementById(options.submitLabelId);
        if (label) label.textContent = options.createLabel;
        const cancel = document.getElementById(options.cancelId);
        if (cancel) cancel.classList.add('hidden');
        showError(options.errorElId, '');
        if (options.onReset) options.onReset();
    }

    async function uploadFile(url, file) {
        if (!url || !file) return false;
        const fd = new FormData();
        fd.set('arquivo', file);
        return postFormData(url, fd);
    }

    function renderEmptyState(container, message) {
        if (!container) return;
        container.innerHTML = `<div class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">${message}</div>`;
    }

    function populateLoteSelect(selectId, lotes, emptyLabel) {
        const select = document.getElementById(selectId);
        if (!select) return;
        if (!Array.isArray(lotes) || lotes.length === 0) {
            select.innerHTML = `<option value="">${emptyLabel}</option>`;
            return;
        }
        select.innerHTML = lotes
            .map((lote) => `<option value="${lote.id}">${lote.mes_referencia} - ${lote.empresa_parceira}</option>`)
            .join('');
    }

    const formAulas = document.getElementById('nx-form-aulas');
    const formAdmin = document.getElementById('nx-form-admin');
    const formDespesas = document.getElementById('nx-form-despesas');
    const formParceriasLote = document.getElementById('nx-form-parcerias-lote');
    const formParceriasItem = document.getElementById('nx-form-parcerias-item');
    const formEngenhariaLote = document.getElementById('nx-form-engenharia-lote');
    const formEngenhariaItem = document.getElementById('nx-form-engenharia-item');
    const despesaFixaField = document.getElementById('nx-despesa-fixa');

    const aulasFormOptions = {
        submitLabelId: 'nx-form-aulas-submit-label',
        cancelId: 'nx-form-aulas-cancel',
        errorElId: 'nx-form-aulas-error',
        createLabel: 'Lançar aula',
        editLabel: 'Salvar aula',
    };
    const adminFormOptions = {
        submitLabelId: 'nx-form-admin-submit-label',
        cancelId: 'nx-form-admin-cancel',
        errorElId: 'nx-form-admin-error',
        createLabel: 'Lançar serviço',
        editLabel: 'Salvar serviço',
    };
    const despesasFormOptions = {
        submitLabelId: 'nx-form-despesas-submit-label',
        cancelId: 'nx-form-despesas-cancel',
        errorElId: 'nx-form-despesas-error',
        createLabel: 'Lançar despesa',
        editLabel: 'Salvar despesa',
        onEnterEdit: () => {
            if (despesaFixaField) {
                despesaFixaField.checked = false;
                despesaFixaField.disabled = true;
            }
        },
        onReset: () => {
            if (despesaFixaField) despesaFixaField.disabled = false;
        },
    };
    const parceriasLoteFormOptions = {
        submitLabelId: 'nx-form-parcerias-lote-submit-label',
        cancelId: 'nx-form-parcerias-lote-cancel',
        errorElId: 'nx-form-parcerias-lote-error',
        createLabel: 'Criar lote',
        editLabel: 'Salvar lote',
    };
    const parceriasItemFormOptions = {
        submitLabelId: 'nx-form-parcerias-item-submit-label',
        cancelId: 'nx-form-parcerias-item-cancel',
        errorElId: 'nx-form-parcerias-item-error',
        createLabel: 'Adicionar serviço',
        editLabel: 'Salvar serviço',
    };
    const engenhariaLoteFormOptions = {
        submitLabelId: 'nx-form-engenharia-lote-submit-label',
        cancelId: 'nx-form-engenharia-lote-cancel',
        errorElId: 'nx-form-engenharia-lote-error',
        createLabel: 'Criar lote',
        editLabel: 'Salvar lote',
    };
    const engenhariaItemFormOptions = {
        submitLabelId: 'nx-form-engenharia-item-submit-label',
        cancelId: 'nx-form-engenharia-item-cancel',
        errorElId: 'nx-form-engenharia-item-error',
        createLabel: 'Adicionar item',
        editLabel: 'Salvar item',
    };

    bindFilterControls('aulas', renderAulas, {
        searchId: 'nx-filter-aulas-q',
        limitId: 'nx-filter-aulas-limit',
        sortId: 'nx-filter-aulas-sort',
        dirId: 'nx-filter-aulas-dir',
    });
    bindFilterControls('admin_direto', renderAdmin, {
        searchId: 'nx-filter-admin-q',
        limitId: 'nx-filter-admin-limit',
        sortId: 'nx-filter-admin-sort',
        dirId: 'nx-filter-admin-dir',
    });
    bindFilterControls('despesas', renderDespesas, {
        searchId: 'nx-filter-despesas-q',
        limitId: 'nx-filter-despesas-limit',
        sortId: 'nx-filter-despesas-sort',
        dirId: 'nx-filter-despesas-dir',
    });
    bindFilterControls('parcerias', renderParcerias, {
        searchId: 'nx-filter-parcerias-q',
        limitId: 'nx-filter-parcerias-limit',
        sortId: 'nx-filter-parcerias-sort',
        dirId: 'nx-filter-parcerias-dir',
    });
    bindFilterControls('engenharia', renderEngenharia, {
        searchId: 'nx-filter-engenharia-q',
        limitId: 'nx-filter-engenharia-limit',
        sortId: 'nx-filter-engenharia-sort',
        dirId: 'nx-filter-engenharia-dir',
    });
    bindFilterControls('notas', renderNotas, {
        searchId: 'nx-filter-notas-q',
        limitId: 'nx-filter-notas-limit',
        sortId: 'nx-filter-notas-sort',
        dirId: 'nx-filter-notas-dir',
    });
    const notasStatusEl = document.getElementById('nx-notas-status');
    if (notasStatusEl) {
        notasStatusEl.value = listState.notas.status;
    }

    async function renderAulas() {
        const data = await loadLista('aulas');
        const tbody = document.getElementById('nx-tbody-aulas');
        if (!tbody) return;
        const items = data?.items ?? [];
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Nenhum lançamento.</td></tr>`;
            renderPagination(data?.meta, 'nx-pagination-aulas', 'aulas', renderAulas);
            return;
        }
        tbody.innerHTML = items
            .map((r) => {
                const lucroClass = Number(r.lucro) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                const del = destroyUrl(destroyAulasUrl, r.id);
                const actions = [
                    `<button data-nx-edit="${r.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar</button>`,
                    del ? `<button data-nx-del="${del}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir</button>` : '',
                ].filter(Boolean).join(' ');
                return `
                    <tr class="border-t border-slate-200/70 dark:border-slate-800">
                        <td class="px-4 py-3">${fmtDate(r.data_lancamento)}${r.data_pagamento ? `<div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Pago: ${fmtDate(r.data_pagamento)}</div>` : ''}</td>
                        <td class="px-4 py-3 text-center">${r.qtd_alunos ?? 0}</td>
                        <td class="px-4 py-3 text-right font-semibold">${toCurrency(r.receita)}</td>
                        <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-400">${toCurrency(r.custo_total)}</td>
                        <td class="px-4 py-3 text-right font-bold ${lucroClass}">${toCurrency(r.lucro)}</td>
                        <td class="px-4 py-3 text-center">${actions}</td>
                    </tr>
                `;
            })
            .join('');
        const byId = new Map(items.map((item) => [String(item.id), item]));
        tbody.querySelectorAll('[data-nx-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = byId.get(String(btn.dataset.nxEdit || ''));
                if (!row || !formAulas) return;
                fillForm(formAulas, row);
                setEditingState(formAulas, {
                    ...aulasFormOptions,
                    id: row.id,
                    url: routeWithId(updateAulasUrl, row.id),
                });
            });
        });
        tbody.querySelectorAll('[data-nx-del]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!confirm('Excluir lançamento?')) return;
                const ok = await handleDelete(btn.dataset.nxDel);
                if (ok) {
                    await Promise.all([renderAulas(), refreshAll()]);
                }
            });
        });
        renderPagination(data?.meta, 'nx-pagination-aulas', 'aulas', renderAulas);
    }

    async function renderAdmin() {
        const data = await loadLista('admin_direto');
        const tbody = document.getElementById('nx-tbody-admin');
        if (!tbody) return;
        const items = data?.items ?? [];
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-6 text-center text-slate-500">Nenhum lançamento.</td></tr>`;
            renderPagination(data?.meta, 'nx-pagination-admin_direto', 'admin_direto', renderAdmin);
            return;
        }
        tbody.innerHTML = items
            .map((r) => {
                const lucroClass = Number(r.lucro) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                const statusBadge =
                    r.status_pagamento === 'Pago'
                        ? `<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">Pago</span>`
                        : `<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">Em aberto</span>`;
                const upload = routeWithId(uploadAdminUrl, r.id);
                const comprovante = r.comprovante_url
                    ? `<div class="flex flex-col items-center gap-1"><a class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400" href="${r.comprovante_url}">Baixar</a><label class="cursor-pointer text-xs font-semibold text-slate-600 hover:underline dark:text-slate-300"><input type="file" accept="image/*,application/pdf" data-nx-upload="${upload}" class="hidden">Trocar</label></div>`
                    : `<label class="cursor-pointer text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><input type="file" accept="image/*,application/pdf" data-nx-upload="${upload}" class="hidden">Anexar</label>`;
                const del = destroyUrl(destroyAdminUrl, r.id);
                const actions = [
                    `<button data-nx-edit="${r.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar</button>`,
                    del ? `<button data-nx-del="${del}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir</button>` : '',
                ].filter(Boolean).join(' ');
                return `
                    <tr class="border-t border-slate-200/70 dark:border-slate-800">
                        <td class="px-4 py-3">${fmtDate(r.data_servico)}${r.data_pagamento ? `<div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Pago: ${fmtDate(r.data_pagamento)}</div>` : ''}</td>
                        <td class="px-4 py-3 font-semibold">${r.cliente_nome ?? ''}</td>
                        <td class="px-4 py-3">${r.servico_tipo ?? ''}</td>
                        <td class="px-4 py-3 text-center">${statusBadge}</td>
                        <td class="px-4 py-3 text-right font-semibold">${toCurrency(r.receita)}</td>
                        <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-400">${toCurrency(r.custo_total)}</td>
                        <td class="px-4 py-3 text-right font-bold ${lucroClass}">${toCurrency(r.lucro)}</td>
                        <td class="px-4 py-3 text-center">${comprovante}</td>
                        <td class="px-4 py-3 text-center">${actions}</td>
                    </tr>
                `;
            })
            .join('');
        const byId = new Map(items.map((item) => [String(item.id), item]));
        tbody.querySelectorAll('[data-nx-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = byId.get(String(btn.dataset.nxEdit || ''));
                if (!row || !formAdmin) return;
                fillForm(formAdmin, row);
                setEditingState(formAdmin, {
                    ...adminFormOptions,
                    id: row.id,
                    url: routeWithId(updateAdminUrl, row.id),
                });
            });
        });
        tbody.querySelectorAll('[data-nx-upload]').forEach((input) => {
            input.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) return;
                const ok = await uploadFile(input.dataset.nxUpload, file);
                input.value = '';
                if (ok) {
                    await Promise.all([renderAdmin(), refreshAll()]);
                }
            });
        });
        tbody.querySelectorAll('[data-nx-del]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!confirm('Excluir lançamento?')) return;
                const ok = await handleDelete(btn.dataset.nxDel);
                if (ok) {
                    await Promise.all([renderAdmin(), refreshAll()]);
                }
            });
        });
        renderPagination(data?.meta, 'nx-pagination-admin_direto', 'admin_direto', renderAdmin);
    }

    async function renderDespesas() {
        const data = await loadLista('despesas');
        const tbody = document.getElementById('nx-tbody-despesas');
        if (!tbody) return;
        const items = data?.items ?? [];
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Nenhum lançamento.</td></tr>`;
            renderPagination(data?.meta, 'nx-pagination-despesas', 'despesas', renderDespesas);
            return;
        }
        tbody.innerHTML = items
            .map((r) => {
                const upload = routeWithId(uploadDespesasUrl, r.id);
                const nota = r.nota_url
                    ? `<div class="flex flex-col items-center gap-1"><a class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400" href="${r.nota_url}">Baixar</a><label class="cursor-pointer text-xs font-semibold text-slate-600 hover:underline dark:text-slate-300"><input type="file" accept="image/*,application/pdf" data-nx-upload="${upload}" class="hidden">Trocar</label></div>`
                    : `<label class="cursor-pointer text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><input type="file" accept="image/*,application/pdf" data-nx-upload="${upload}" class="hidden">Anexar</label>`;
                const del = destroyUrl(destroyDespesasUrl, r.id);
                const actions = [
                    `<button data-nx-edit="${r.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar</button>`,
                    del ? `<button data-nx-del="${del}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir</button>` : '',
                ].filter(Boolean).join(' ');
                return `
                    <tr class="border-t border-slate-200/70 dark:border-slate-800">
                        <td class="px-4 py-3">${fmtDate(r.data_lancamento)}${r.data_pagamento ? `<div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Pago: ${fmtDate(r.data_pagamento)}</div>` : ''}</td>
                        <td class="px-4 py-3">${r.descricao ?? ''}</td>
                        <td class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-400">- ${toCurrency(r.valor)}</td>
                        <td class="px-4 py-3 text-center">${nota}</td>
                        <td class="px-4 py-3 text-center">${actions}</td>
                    </tr>
                `;
            })
            .join('');
        const byId = new Map(items.map((item) => [String(item.id), item]));
        tbody.querySelectorAll('[data-nx-edit]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = byId.get(String(btn.dataset.nxEdit || ''));
                if (!row || !formDespesas) return;
                fillForm(formDespesas, row);
                setEditingState(formDespesas, {
                    ...despesasFormOptions,
                    id: row.id,
                    url: routeWithId(updateDespesasUrl, row.id),
                });
            });
        });
        tbody.querySelectorAll('[data-nx-upload]').forEach((input) => {
            input.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) return;
                const ok = await uploadFile(input.dataset.nxUpload, file);
                input.value = '';
                if (ok) {
                    await Promise.all([renderDespesas(), refreshAll()]);
                }
            });
        });
        tbody.querySelectorAll('[data-nx-del]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!confirm('Excluir lançamento?')) return;
                const ok = await handleDelete(btn.dataset.nxDel);
                if (ok) {
                    await Promise.all([renderDespesas(), refreshAll()]);
                }
            });
        });
        renderPagination(data?.meta, 'nx-pagination-despesas', 'despesas', renderDespesas);
    }

    async function renderParcerias() {
        const data = await loadLista('parcerias');
        const container = document.getElementById('nx-parcerias-lista');
        const lotes = data?.items ?? [];
        populateLoteSelect('nx-parcerias-lote-id', lotes, 'Nenhum lote cadastrado');
        if (!container) return;
        if (lotes.length === 0) {
            renderEmptyState(container, 'Nenhum lote cadastrado.');
            renderPagination(data?.meta, 'nx-pagination-parcerias', 'parcerias', renderParcerias);
            return;
        }

        container.innerHTML = lotes
            .map((lote) => {
                const loteDel = destroyUrl(destroyParceriasUrl, lote.id);
                const loteUpload = routeWithId(uploadParceriasUrl, lote.id);
                const comprovante = lote.comprovante_url
                    ? `<div class="flex flex-col items-end gap-1"><a class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400" href="${lote.comprovante_url}">Comprovante</a><label class="cursor-pointer text-xs font-semibold text-slate-600 hover:underline dark:text-slate-300"><input type="file" accept="image/*,application/pdf" data-nx-upload="${loteUpload}" class="hidden">Trocar</label></div>`
                    : `<label class="cursor-pointer text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><input type="file" accept="image/*,application/pdf" data-nx-upload="${loteUpload}" class="hidden">Anexar</label>`;
                const items = Array.isArray(lote.servicos) ? lote.servicos : [];
                const itemsHtml = items.length
                    ? items
                          .map((item) => {
                              const itemDel = destroyUrl(destroyParceriaItemUrl, item.id);
                              const itemActions = [
                                  `<button data-nx-edit-item="${item.id}" data-nx-lote-id="${lote.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar</button>`,
                                  itemDel ? `<button data-nx-del="${itemDel}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir</button>` : '',
                              ].filter(Boolean).join(' ');
                              const nota = item.nota_emitida
                                  ? `<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">Emitida</span>`
                                  : `<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">Pendente</span>`;
                              return `
                                  <tr class="border-t border-slate-200/70 dark:border-slate-800">
                                      <td class="px-3 py-2">${fmtDate(item.data_lancamento)}</td>
                                      <td class="px-3 py-2 font-medium">${item.cliente_nome ?? ''}</td>
                                      <td class="px-3 py-2">${item.servico_tipo ?? ''}</td>
                                      <td class="px-3 py-2 text-right">${toCurrency(item.receita)}</td>
                                      <td class="px-3 py-2 text-right">${toCurrency(item.lucro)}</td>
                                      <td class="px-3 py-2 text-center">${nota}</td>
                                      <td class="px-3 py-2 text-center">${itemActions}</td>
                                  </tr>
                              `;
                          })
                          .join('')
                    : `<tr><td colspan="7" class="px-3 py-4 text-center text-sm text-slate-500">Nenhum serviço neste lote.</td></tr>`;

                return `
                    <section class="rounded-xl border border-slate-200 dark:border-slate-800">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">${lote.empresa_parceira}</h3>
                                    <span class="text-xs font-semibold text-slate-500">${lote.mes_referencia}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Itens: ${lote.items_count ?? 0} | Receita: ${toCurrency(lote.receita_total)} | Lucro: ${toCurrency(lote.lucro_total)}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                ${comprovante}
                                <button data-nx-edit-lote="${lote.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar lote</button>
                                ${loteDel ? `<button data-nx-del="${loteDel}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir lote</button>` : ''}
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                    <tr>
                                        <th class="px-3 py-2">Data</th>
                                        <th class="px-3 py-2">Cliente</th>
                                        <th class="px-3 py-2">Serviço</th>
                                        <th class="px-3 py-2 text-right">Receita</th>
                                        <th class="px-3 py-2 text-right">Lucro</th>
                                        <th class="px-3 py-2 text-center">Nota</th>
                                        <th class="px-3 py-2 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>${itemsHtml}</tbody>
                            </table>
                        </div>
                    </section>
                `;
            })
            .join('');

        const lotesById = new Map(lotes.map((lote) => [String(lote.id), lote]));
        const itemsById = new Map(lotes.flatMap((lote) => (Array.isArray(lote.servicos) ? lote.servicos : []).map((item) => [String(item.id), { ...item, lote_id: lote.id }])));

        container.querySelectorAll('[data-nx-edit-lote]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const lote = lotesById.get(String(btn.dataset.nxEditLote || ''));
                if (!lote || !formParceriasLote) return;
                fillForm(formParceriasLote, lote);
                setEditingState(formParceriasLote, {
                    ...parceriasLoteFormOptions,
                    id: lote.id,
                    url: routeWithId(updateParceriasUrl, lote.id),
                });
            });
        });
        container.querySelectorAll('[data-nx-edit-item]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const item = itemsById.get(String(btn.dataset.nxEditItem || ''));
                if (!item || !formParceriasItem) return;
                fillForm(formParceriasItem, { ...item, lote_id: item.lote_id });
                setEditingState(formParceriasItem, {
                    ...parceriasItemFormOptions,
                    id: item.id,
                    url: routeWithId(updateParceriaItemUrl, item.id),
                });
            });
        });
        container.querySelectorAll('[data-nx-upload]').forEach((input) => {
            input.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) return;
                const ok = await uploadFile(input.dataset.nxUpload, file);
                input.value = '';
                if (ok) {
                    await Promise.all([renderParcerias(), refreshAll()]);
                }
            });
        });
        container.querySelectorAll('[data-nx-del]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!confirm('Excluir registro?')) return;
                const ok = await handleDelete(btn.dataset.nxDel);
                if (ok) {
                    await Promise.all([renderParcerias(), renderNotas(), refreshAll()]);
                }
            });
        });
        renderPagination(data?.meta, 'nx-pagination-parcerias', 'parcerias', renderParcerias);
    }

    async function renderEngenharia() {
        const data = await loadLista('engenharia');
        const container = document.getElementById('nx-engenharia-lista');
        const lotes = data?.items ?? [];
        populateLoteSelect('nx-engenharia-lote-id', lotes, 'Nenhum lote cadastrado');
        if (!container) return;
        if (lotes.length === 0) {
            renderEmptyState(container, 'Nenhum lote cadastrado.');
            renderPagination(data?.meta, 'nx-pagination-engenharia', 'engenharia', renderEngenharia);
            return;
        }

        container.innerHTML = lotes
            .map((lote) => {
                const loteDel = destroyUrl(destroyEngenhariaUrl, lote.id);
                const loteUpload = routeWithId(uploadEngenhariaUrl, lote.id);
                const comprovante = lote.comprovante_url
                    ? `<div class="flex flex-col items-end gap-1"><a class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400" href="${lote.comprovante_url}">Comprovante</a><label class="cursor-pointer text-xs font-semibold text-slate-600 hover:underline dark:text-slate-300"><input type="file" accept="image/*,application/pdf" data-nx-upload="${loteUpload}" class="hidden">Trocar</label></div>`
                    : `<label class="cursor-pointer text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><input type="file" accept="image/*,application/pdf" data-nx-upload="${loteUpload}" class="hidden">Anexar</label>`;
                const items = Array.isArray(lote.servicos) ? lote.servicos : [];
                const itemsHtml = items.length
                    ? items
                          .map((item) => {
                              const itemDel = destroyUrl(destroyEngenhariaItemUrl, item.id);
                              const itemActions = [
                                  `<button data-nx-edit-item="${item.id}" data-nx-lote-id="${lote.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar</button>`,
                                  itemDel ? `<button data-nx-del="${itemDel}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir</button>` : '',
                              ].filter(Boolean).join(' ');
                              const nota = item.nota_emitida
                                  ? `<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">Emitida</span>`
                                  : `<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">Pendente</span>`;
                              return `
                                  <tr class="border-t border-slate-200/70 dark:border-slate-800">
                                      <td class="px-3 py-2">${fmtDate(item.data_lancamento)}</td>
                                      <td class="px-3 py-2 font-medium">${item.cliente_nome ?? ''}</td>
                                      <td class="px-3 py-2">${item.servico_tipo ?? ''}</td>
                                      <td class="px-3 py-2 text-right">${toCurrency(item.receita)}</td>
                                      <td class="px-3 py-2 text-right">${toCurrency(item.lucro)}</td>
                                      <td class="px-3 py-2 text-center">${nota}</td>
                                      <td class="px-3 py-2 text-center">${itemActions}</td>
                                  </tr>
                              `;
                          })
                          .join('')
                    : `<tr><td colspan="7" class="px-3 py-4 text-center text-sm text-slate-500">Nenhum item neste lote.</td></tr>`;

                return `
                    <section class="rounded-xl border border-slate-200 dark:border-slate-800">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">${lote.empresa_parceira}</h3>
                                    <span class="text-xs font-semibold text-slate-500">${lote.mes_referencia}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Itens: ${lote.items_count ?? 0} | Receita: ${toCurrency(lote.receita_total)} | Lucro: ${toCurrency(lote.lucro_total)}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                ${comprovante}
                                <button data-nx-edit-lote="${lote.id}" class="rounded-lg bg-slate-700 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-600">Editar lote</button>
                                ${loteDel ? `<button data-nx-del="${loteDel}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Excluir lote</button>` : ''}
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                    <tr>
                                        <th class="px-3 py-2">Data</th>
                                        <th class="px-3 py-2">Cliente</th>
                                        <th class="px-3 py-2">Projeto/Serviço</th>
                                        <th class="px-3 py-2 text-right">Receita</th>
                                        <th class="px-3 py-2 text-right">Lucro</th>
                                        <th class="px-3 py-2 text-center">Nota</th>
                                        <th class="px-3 py-2 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>${itemsHtml}</tbody>
                            </table>
                        </div>
                    </section>
                `;
            })
            .join('');

        const lotesById = new Map(lotes.map((lote) => [String(lote.id), lote]));
        const itemsById = new Map(lotes.flatMap((lote) => (Array.isArray(lote.servicos) ? lote.servicos : []).map((item) => [String(item.id), { ...item, lote_id: lote.id }])));

        container.querySelectorAll('[data-nx-edit-lote]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const lote = lotesById.get(String(btn.dataset.nxEditLote || ''));
                if (!lote || !formEngenhariaLote) return;
                fillForm(formEngenhariaLote, lote);
                setEditingState(formEngenhariaLote, {
                    ...engenhariaLoteFormOptions,
                    id: lote.id,
                    url: routeWithId(updateEngenhariaUrl, lote.id),
                });
            });
        });
        container.querySelectorAll('[data-nx-edit-item]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const item = itemsById.get(String(btn.dataset.nxEditItem || ''));
                if (!item || !formEngenhariaItem) return;
                fillForm(formEngenhariaItem, { ...item, lote_id: item.lote_id });
                setEditingState(formEngenhariaItem, {
                    ...engenhariaItemFormOptions,
                    id: item.id,
                    url: routeWithId(updateEngenhariaItemUrl, item.id),
                });
            });
        });
        container.querySelectorAll('[data-nx-upload]').forEach((input) => {
            input.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) return;
                const ok = await uploadFile(input.dataset.nxUpload, file);
                input.value = '';
                if (ok) {
                    await Promise.all([renderEngenharia(), refreshAll()]);
                }
            });
        });
        container.querySelectorAll('[data-nx-del]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!confirm('Excluir registro?')) return;
                const ok = await handleDelete(btn.dataset.nxDel);
                if (ok) {
                    await Promise.all([renderEngenharia(), renderNotas(), refreshAll()]);
                }
            });
        });
        renderPagination(data?.meta, 'nx-pagination-engenharia', 'engenharia', renderEngenharia);
    }

    async function renderNotas() {
        const tbody = document.getElementById('nx-tbody-notas');
        const statusEl = document.getElementById('nx-notas-status');
        if (!tbody || !notasUrl) return;
        const state = listState.notas;

        const url = new URL(notasUrl, window.location.origin);
        url.searchParams.set('ano', anoEl?.value || String(now.getFullYear()));
        url.searchParams.set('mes', mesEl?.value || 'todos');
        url.searchParams.set('status', statusEl?.value || state.status || 'todos');
        url.searchParams.set('q', state.q || '');
        url.searchParams.set('page', String(state.page || 1));
        url.searchParams.set('limit', String(state.limit || 25));
        if (state.sort) url.searchParams.set('sort', String(state.sort));
        if (state.dir) url.searchParams.set('dir', String(state.dir));

        const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        const items = data?.items ?? [];

        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Nenhum registro para este filtro.</td></tr>`;
            renderPagination(data?.meta, 'nx-pagination-notas', 'notas', renderNotas);
            return;
        }

        tbody.innerHTML = items
            .map((item) => {
                const statusBadge = item.nota_emitida
                    ? `<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">Emitida</span>`
                    : `<span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">Pendente</span>`;
                const action = item.nota_emitida
                    ? `<span class="text-xs text-slate-400">—</span>`
                    : `<button data-nx-emitir="${item.record_type}:${item.record_id}" class="rounded-lg bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Marcar emitida</button>`;
                return `
                    <tr class="border-t border-slate-200/70 dark:border-slate-800">
                        <td class="px-4 py-3">${fmtDate(item.data)}</td>
                        <td class="px-4 py-3">${item.modulo}</td>
                        <td class="px-4 py-3 font-medium">${item.cliente_nome ?? item.empresa_parceira ?? ''}</td>
                        <td class="px-4 py-3">${item.servico_tipo ?? ''}</td>
                        <td class="px-4 py-3 text-right font-semibold">${toCurrency(item.receita)}</td>
                        <td class="px-4 py-3 text-center">${statusBadge}</td>
                        <td class="px-4 py-3 text-center">${action}</td>
                    </tr>
                `;
            })
            .join('');

        tbody.querySelectorAll('[data-nx-emitir]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const [recordType, recordId] = String(btn.dataset.nxEmitir || '').split(':');
                if (!recordType || !recordId) return;
                const fd = new FormData();
                fd.set('record_type', recordType);
                fd.set('record_id', recordId);
                const ok = await postFormData(emitirNotaUrl, fd);
                if (ok) {
                    await Promise.all([renderNotas(), renderParcerias(), renderEngenharia(), refreshAll()]);
                }
            });
        });
        renderPagination(data?.meta, 'nx-pagination-notas', 'notas', renderNotas);
    }

    document.querySelectorAll('[data-nx-fin-tab]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const tab = btn.dataset.nxFinTab;
            if (!tab) return;
            setTab(tab);
            if (tab === 'aulas') await renderAulas();
            if (tab === 'admin_direto') await renderAdmin();
            if (tab === 'despesas') await renderDespesas();
            if (tab === 'parcerias') await renderParcerias();
            if (tab === 'engenharia') await renderEngenharia();
            if (tab === 'notas') await renderNotas();
        });
    });

    formAulas?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = formAulas.dataset.submitUrl || storeAulasUrl;
        const method = formAulas.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formAulas, url, aulasFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formAulas, aulasFormOptions);
            await Promise.all([renderAulas(), refreshAll()]);
        }
    });

    formAdmin?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = formAdmin.dataset.submitUrl || storeAdminUrl;
        const method = formAdmin.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formAdmin, url, adminFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formAdmin, adminFormOptions);
            await Promise.all([renderAdmin(), renderNotas(), refreshAll()]);
        }
    });

    formDespesas?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = formDespesas.dataset.submitUrl || storeDespesasUrl;
        const method = formDespesas.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formDespesas, url, despesasFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formDespesas, despesasFormOptions);
            await Promise.all([renderDespesas(), refreshAll()]);
        }
    });

    formParceriasLote?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = formParceriasLote.dataset.submitUrl || storeParceriasUrl;
        const method = formParceriasLote.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formParceriasLote, url, parceriasLoteFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formParceriasLote, parceriasLoteFormOptions);
            await Promise.all([renderParcerias(), renderNotas(), refreshAll()]);
        }
    });

    formParceriasItem?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const loteId = document.getElementById('nx-parcerias-lote-id')?.value;
        const url = formParceriasItem.dataset.submitUrl || routeWithId(storeParceriaItemUrl, loteId);
        const method = formParceriasItem.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formParceriasItem, url, parceriasItemFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formParceriasItem, parceriasItemFormOptions);
            await Promise.all([renderParcerias(), renderNotas(), refreshAll()]);
        }
    });

    formEngenhariaLote?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = formEngenhariaLote.dataset.submitUrl || storeEngenhariaUrl;
        const method = formEngenhariaLote.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formEngenhariaLote, url, engenhariaLoteFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formEngenhariaLote, engenhariaLoteFormOptions);
            await Promise.all([renderEngenharia(), renderNotas(), refreshAll()]);
        }
    });

    formEngenhariaItem?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const loteId = document.getElementById('nx-engenharia-lote-id')?.value;
        const url = formEngenhariaItem.dataset.submitUrl || routeWithId(storeEngenhariaItemUrl, loteId);
        const method = formEngenhariaItem.dataset.submitMethod || 'POST';
        const ok = await handleCreate(formEngenhariaItem, url, engenhariaItemFormOptions.errorElId, method);
        if (ok) {
            resetEditingState(formEngenhariaItem, engenhariaItemFormOptions);
            await Promise.all([renderEngenharia(), renderNotas(), refreshAll()]);
        }
    });

    document.getElementById(aulasFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formAulas, aulasFormOptions));
    document.getElementById(adminFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formAdmin, adminFormOptions));
    document.getElementById(despesasFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formDespesas, despesasFormOptions));
    document.getElementById(parceriasLoteFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formParceriasLote, parceriasLoteFormOptions));
    document.getElementById(parceriasItemFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formParceriasItem, parceriasItemFormOptions));
    document.getElementById(engenhariaLoteFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formEngenhariaLote, engenhariaLoteFormOptions));
    document.getElementById(engenhariaItemFormOptions.cancelId)?.addEventListener('click', () => resetEditingState(formEngenhariaItem, engenhariaItemFormOptions));

    document.getElementById('nx-notas-status')?.addEventListener('change', async (e) => {
        setState('notas', { status: e.target.value, page: 1 });
        await renderNotas();
    });

    setTab('overview');
}

