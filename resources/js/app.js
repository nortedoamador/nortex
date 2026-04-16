import './bootstrap';
import './escola-turbo';
import { registerNxAulasIndex } from './aulas-nautica-index';

registerNxAulasIndex();

import { initClienteFichaMasks } from './cliente-ficha-masks';
import { initClienteFichaDoc } from './cliente-ficha-doc';
import { initClienteFichaGeo } from './cliente-ficha-geo';
import { initDateBrMasks } from './date-br-mask';
import { initCnpjMasks } from './cnpj-mask';
import { registerEmbarcacaoFotoDrop } from './embarcacao-foto-drop';
import { initEmbarcacaoGaleriaDelete } from './embarcacao-galeria-delete';
import { registerEmbarcacaoCpfSuggest } from './embarcacao-cpf-suggest';
import { registerNovoProcessoModal } from './novo-processo-modal';
import { initProcessoStatusFormConfirm } from './processo-status-form-confirm';
import { registerProcessosBulkActions } from './processos-bulk-actions';
import { registerProcessoStatusCustomSelect } from './processo-status-custom-select';
import { registerProcessoPostIts } from './processo-post-its';
import './platform-dashboard-map';
import { initTipoProcessoRegrasSort } from './tipo-processo-regras-sort';
import { initFinanceiroPage } from './financeiro';
import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    registerEmbarcacaoFotoDrop(Alpine);
    registerEmbarcacaoCpfSuggest(Alpine);
    registerNovoProcessoModal(Alpine);
    registerProcessosBulkActions(Alpine);
    registerProcessoStatusCustomSelect(Alpine);
    registerProcessoPostIts(Alpine);

    Alpine.store('novoCliente', {
        open: false,
    });

    Alpine.store('novaEmbarcacao', {
        open: false,
    });

    Alpine.store('novaHabilitacao', {
        open: false,
    });

    Alpine.store('novaAula', {
        open: false,
    });

    Alpine.store('novoProcesso', {
        open: false,
        /** @type {null | { origemFichaEmbarcacao?: boolean, categoria?: string, clienteId?: string|number|null, embarcacaoId?: string|number|null, clienteDoc?: string|null, clienteNome?: string|null }} */
        preset: null,
    });

    Alpine.data('habilitacaoForm', (byId, idPrefix = '') => ({
        byId,
        idPrefix,
        fillCliente(id) {
            if (! id || ! this.byId[id]) {
                return;
            }
            const c = this.byId[id];
            const nomeEl = document.getElementById(this.idPrefix + 'nome');
            const cpfEl = document.getElementById(this.idPrefix + 'cpf');
            if (nomeEl) {
                nomeEl.value = c.nome ?? '';
            }
            if (! cpfEl) {
                return;
            }
            const d = c.cpf_digits || '';
            cpfEl.value =
                d.length === 11
                    ? `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9, 11)}`
                    : (c.cpf_display || '');
        },
    }));
});

function bindClienteFichaForm(form) {
    if (!form || form.dataset.nxClienteFichaReady === '1') {
        return;
    }
    form.dataset.nxClienteFichaReady = '1';
    initClienteFichaMasks(form);
    initClienteFichaDoc(form);
    initClienteFichaGeo(form);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-cliente-ficha]').forEach((form) => {
        bindClienteFichaForm(form);
    });
    document.querySelectorAll('form[data-nx-checklist-sort]').forEach((form) => {
        initTipoProcessoRegrasSort(form);
    });
    initDateBrMasks(document);
    initCnpjMasks(document);
    initProcessoStatusFormConfirm();
    initEmbarcacaoGaleriaDelete();
    initFinanceiroPage();
});

function applyTheme(theme) {
    const root = document.documentElement;
    root.classList.toggle('dark', theme === 'dark');
    root.dataset.theme = theme;
}

Alpine.store('theme', {
    theme: 'system',
    init() {
        const saved = localStorage.getItem('nx_theme');
        this.theme = saved ?? 'system';

        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const resolve = () => (this.theme === 'system' ? (media.matches ? 'dark' : 'light') : this.theme);

        applyTheme(resolve());
        media.addEventListener('change', () => applyTheme(resolve()));
    },
    set(theme) {
        this.theme = theme;
        localStorage.setItem('nx_theme', theme);

        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(theme === 'system' ? (isDark ? 'dark' : 'light') : theme);
    },
    toggle() {
        this.set(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
    },
});

window.Alpine = Alpine;

Alpine.start();
