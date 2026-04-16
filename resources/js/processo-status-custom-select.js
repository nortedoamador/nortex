/**
 * Select nativo oculto + painel fixo (ícone + rótulo) para etapas do processo.
 * Mantém select[name=status] para processo-status-form-confirm.js e submissão do formulário.
 */
export function registerProcessoStatusCustomSelect(Alpine) {
    Alpine.data('nxProcessoStatusCustomSelect', (config) => ({
        open: false,
        value: config.initialValue,
        labels: config.labels && typeof config.labels === 'object' ? config.labels : {},
        panelStyle: '',

        init() {
            this.syncFromNative();
        },

        syncFromNative() {
            const sel = this.$refs.nativeSelect;
            if (sel && typeof sel.value === 'string') {
                this.value = sel.value;
            }
        },

        get label() {
            return this.labels[this.value] ?? '';
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.updatePanelPosition());
            }
        },

        updatePanelPosition() {
            const btn = this.$refs.triggerBtn;
            if (!btn) {
                return;
            }
            const r = btn.getBoundingClientRect();
            const w = Math.max(r.width, 1);
            const left = Math.min(r.left, Math.max(12, window.innerWidth - w - 12));
            this.panelStyle = `top:${r.bottom + 8}px;left:${left}px;width:${w}px;`;
        },

        choose(v) {
            this.open = false;
            this.value = v;
            const sel = this.$refs.nativeSelect;
            if (!sel) {
                return;
            }
            if (sel.value === v) {
                return;
            }
            sel.value = v;
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }));
}
