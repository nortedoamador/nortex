/**
 * Autocomplete CPF/CNPJ (Alpine). Lista grande: usar JSON em <script> + nxEmbarcacaoCpfSuggestEl
 * para não colocar @json/@js dentro de atributo HTML (aspas em nomes quebram x-data='...').
 */
function createNxCpfSuggest(items, initialQ, opts) {
    return {
        q: initialQ == null ? '' : String(initialQ),
        open: false,
        items: Array.isArray(items) ? items : [],
        filtered: [],
        highlighted: -1,
        panelStyle: '',
        opts: opts && typeof opts === 'object' ? opts : {},

        init() {
            this._repos = () => {
                if (this.open) {
                    this.syncPanelPos();
                }
            };
            document.addEventListener('scroll', this._repos, true);
            window.addEventListener('resize', this._repos);
        },

        syncPanelPos() {
            this.$nextTick(() => {
                const el = this.$refs.cpfInput;
                if (!el) {
                    return;
                }
                const r = el.getBoundingClientRect();
                this.panelStyle = `position:fixed;top:${r.bottom + 6}px;left:${r.left}px;width:${r.width}px;z-index:9999`;
            });
        },

        filter() {
            const raw = (this.q || '').trim();
            if (!raw) {
                this.filtered = [];
                this.open = false;
                this.highlighted = -1;
                this.panelStyle = '';
                return;
            }
            const t = raw.toLowerCase();
            const digits = raw.replace(/\D/g, '');
            this.filtered = this.items
                .filter((i) => {
                    if (i.nome && String(i.nome).toLowerCase().includes(t)) {
                        return true;
                    }
                    if (i.doc && String(i.doc).toLowerCase().includes(t)) {
                        return true;
                    }
                    if (digits && i.docDigits && String(i.docDigits).includes(digits)) {
                        return true;
                    }
                    return false;
                })
                .slice(0, 12);
            this.open = this.filtered.length > 0;
            this.highlighted = this.open ? 0 : -1;
            if (this.open) {
                this.syncPanelPos();
            } else {
                this.panelStyle = '';
            }
        },

        pick(item) {
            this.q = item.doc;
            this.open = false;
            this.filtered = [];
            this.highlighted = -1;
            this.panelStyle = '';

            if (typeof this.opts.onPick === 'function') {
                try {
                    this.opts.onPick(item);
                } catch (_) {
                    // noop
                }
            }
        },

        onBlur() {
            setTimeout(() => {
                this.open = false;
                this.panelStyle = '';
            }, 180);
        },

        onKeydown(e) {
            if (!this.open || !this.filtered.length) {
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.highlighted = Math.min(this.highlighted + 1, this.filtered.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.highlighted = Math.max(this.highlighted - 1, 0);
            } else if (e.key === 'Enter' && this.highlighted >= 0) {
                e.preventDefault();
                this.pick(this.filtered[this.highlighted]);
            } else if (e.key === 'Escape') {
                this.open = false;
                this.panelStyle = '';
            }
        },
    };
}

export function registerEmbarcacaoCpfSuggest(Alpine) {
    Alpine.data('nxEmbarcacaoCpfSuggest', (items = [], initialQ = '', opts = {}) =>
        createNxCpfSuggest(Array.isArray(items) ? items : [], initialQ, opts),
    );

    /** @param {string} payloadElId id do <textarea> ou <script> com JSON da lista */
    Alpine.data('nxEmbarcacaoCpfSuggestEl', (payloadElId, clienteIdElId = '', nomeElId = '') => {
        const opts = {
            onPick: (item) => {
                if (clienteIdElId) {
                    const el = document.getElementById(clienteIdElId);
                    if (el) {
                        el.value = item.id;
                        try {
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        } catch (_) {}
                    }
                }
                if (nomeElId) {
                    const el = document.getElementById(nomeElId);
                    if (el) {
                        el.value = item.nome ?? '';
                        try {
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        } catch (_) {}
                    }
                }
            },
        };

        const component = createNxCpfSuggest([], '', opts);
        const baseInit = component.init.bind(component);

        component.init = function initFromJson() {
            try {
                const el = document.getElementById(payloadElId);
                if (el?.textContent) {
                    const parsed = JSON.parse(el.textContent);
                    this.items = Array.isArray(parsed) ? parsed : [];
                }
            } catch (_) {
                this.items = [];
            }

            const iq = this.$el?.dataset?.nxInitialQ;
            if (iq != null && iq !== '') {
                this.q = String(iq);
            }

            baseInit();
        };

        return component;
    });
}
