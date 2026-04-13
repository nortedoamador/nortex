/**
 * Lista de processos: seleção múltipla, alteração de etapa em lote e exclusão em lote.
 */
export function registerProcessosBulkActions(Alpine) {
    Alpine.data('nxProcessosBulkActions', (cfg) => ({
        cfg: typeof cfg === 'object' && cfg !== null ? cfg : { pageIds: [] },
        selected: {},
        statusDestino: '',

        init() {
            this.selected = {};
            this.statusDestino = '';
        },

        toggle(id, checked) {
            const k = String(id);
            if (checked) {
                this.selected = { ...this.selected, [k]: true };
            } else {
                const { [k]: _removed, ...rest } = this.selected;
                this.selected = rest;
            }
        },

        isChecked(id) {
            return !!this.selected[String(id)];
        },

        get idList() {
            return Object.keys(this.selected).map((x) => Number(x));
        },

        get count() {
            return this.idList.length;
        },

        get countDeletable() {
            const d = new Set((this.cfg.deletableIds || []).map(Number));
            return this.idList.filter((id) => d.has(id)).length;
        },

        get allOnPageSelected() {
            const p = this.cfg.pageIds || [];
            return p.length > 0 && p.every((i) => this.isChecked(i));
        },

        toggleAllOnPage(checked) {
            const p = this.cfg.pageIds || [];
            let next = { ...this.selected };
            p.forEach((id) => {
                const k = String(id);
                if (checked) {
                    next[k] = true;
                } else {
                    delete next[k];
                }
            });
            this.selected = next;
        },

        limparSelecao() {
            this.selected = {};
            this.statusDestino = '';
        },

        idsParaExclusao() {
            const d = new Set((this.cfg.deletableIds || []).map(Number));
            return this.idList.filter((id) => d.has(id));
        },

        async excluirLote() {
            const ids = this.idsParaExclusao();
            if (ids.length === 0) {
                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        icon: 'info',
                        text: this.cfg.msgSemExclusaoLote || '',
                    });
                }
                return;
            }
            if (typeof window.nxSwalExcluirProcesso !== 'function') {
                return;
            }
            const n = ids.length;
            const c = this.cfg;
            const linha =
                n === 1
                    ? String(c.msgLinha1 || '').replace(':count', '1')
                    : String(c.msgLinhaN || '').replace(':count', String(n));
            const pergunta =
                n === 1
                    ? String(c.msgPergunta1 || '')
                    : String(c.msgPerguntaN || '').replace(':count', String(n));

            const r = await window.nxSwalExcluirProcesso({
                titulo: c.swalTitulo || '',
                linhaProcesso: linha,
                fraseAviso: c.swalAviso || '',
                textoPergunta: pergunta,
                confirmButtonText: c.swalBtnSim || '',
                cancelButtonText: c.swalBtnNao || '',
            });

            if (!r || !r.isConfirmed) {
                return;
            }

            const form = document.getElementById('nx-processos-bulk-delete-form');
            if (!form) {
                return;
            }
            form.querySelectorAll('input[name="ids[]"]').forEach((el) => el.remove());
            for (const id of ids) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = String(id);
                form.appendChild(inp);
            }
            form.submit();
        },

        async aplicarEtapaLote() {
            if (!this.cfg.podeAlterarStatus || !this.statusDestino || this.count === 0) {
                return;
            }
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = this.cfg.statusLoteUrl;
            if (!token || !url) {
                return;
            }

            const base = {
                _token: token,
                ids: this.idList,
                status: this.statusDestino,
                redirect_v: this.cfg.redirectV,
                redirect_q: this.cfg.redirectQ,
                redirect_status: this.cfg.redirectStatus,
                ...(this.cfg.redirectExtras && typeof this.cfg.redirectExtras === 'object' ? this.cfg.redirectExtras : {}),
            };

            let confirmarCiencia = false;

            for (;;) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        ...base,
                        confirmar_ciencia_pendencias_documentais: confirmarCiencia,
                    }),
                });

                let data = {};
                try {
                    data = await res.json();
                } catch {
                    data = {};
                }

                if (res.ok && data.ok) {
                    window.location.reload();
                    return;
                }

                if (res.status === 422 && data.needs_ciencia && !confirmarCiencia) {
                    if (typeof window.nxSwalConfirmarCienciaDocumental !== 'function') {
                        break;
                    }
                    const r = await window.nxSwalConfirmarCienciaDocumental({
                        titulo: this.cfg.cienciaTitulo || '',
                        linhaProcesso: String(this.cfg.cienciaLinhaLote || '').replace(':count', String(this.count)),
                        frasePendentes: this.cfg.cienciaFraseLote || '',
                        textoSecundario: this.cfg.cienciaTextoSec || '',
                    });
                    if (!r || !r.isConfirmed) {
                        return;
                    }
                    confirmarCiencia = true;
                    continue;
                }

                const msg = data.message || this.cfg.msgErroLote || '';
                if (typeof window.Swal !== 'undefined' && msg) {
                    window.Swal.fire({ icon: 'error', text: msg });
                }
                return;
            }
        },
    }));
}
