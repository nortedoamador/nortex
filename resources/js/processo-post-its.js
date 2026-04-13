/**
 * Notas rápidas na ficha do processo (CRUD via JSON, sem recarregar a página).
 */
function urlWithId(template, id) {
    return template.replace('__POST_IT__', String(id));
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function nxJsonFetch(url, options = {}) {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {}),
    };
    const res = await fetch(url, { ...options, headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const msg =
            (typeof data.message === 'string' && data.message) ||
            (data.errors && Object.values(data.errors).flat().join(' ')) ||
            '';
        throw new Error(msg || `HTTP ${res.status}`);
    }
    return data;
}

export function registerProcessoPostIts(Alpine) {
    Alpine.data('nxProcessoPostIts', (cfg) => ({
        items: Array.isArray(cfg.items) ? [...cfg.items] : [],
        canEdit: Boolean(cfg.canEdit),
        urls: cfg.urls || {},
        msgConfirmDelete: cfg.msgConfirmDelete || '',
        msgError: cfg.msgError || '',
        draft: '',
        editingId: null,
        editDraft: '',
        busy: false,
        error: '',

        startEdit(it) {
            if (!this.canEdit) {
                return;
            }
            this.editingId = it.id;
            this.editDraft = it.conteudo;
            this.error = '';
        },

        cancelEdit() {
            this.editingId = null;
            this.editDraft = '';
        },

        async add() {
            if (!this.canEdit || this.busy) {
                return;
            }
            const text = this.draft.trim();
            if (!text) {
                return;
            }
            this.busy = true;
            this.error = '';
            try {
                const data = await nxJsonFetch(this.urls.store, {
                    method: 'POST',
                    body: JSON.stringify({ conteudo: text }),
                });
                if (data.post_it) {
                    this.items.push(data.post_it);
                }
                this.draft = '';
            } catch (e) {
                this.error = this.msgError;
            } finally {
                this.busy = false;
            }
        },

        async saveEdit() {
            if (!this.canEdit || this.busy || this.editingId == null) {
                return;
            }
            const text = this.editDraft.trim();
            if (!text) {
                return;
            }
            const id = this.editingId;
            this.busy = true;
            this.error = '';
            try {
                const data = await nxJsonFetch(urlWithId(this.urls.update, id), {
                    method: 'PATCH',
                    body: JSON.stringify({ conteudo: text }),
                });
                const idx = this.items.findIndex((x) => x.id === id);
                if (idx !== -1 && data.post_it) {
                    this.items[idx] = data.post_it;
                }
                this.cancelEdit();
            } catch (e) {
                this.error = this.msgError;
            } finally {
                this.busy = false;
            }
        },

        async remove(id) {
            if (!this.canEdit || this.busy) {
                return;
            }
            if (this.msgConfirmDelete && !window.confirm(this.msgConfirmDelete)) {
                return;
            }
            this.busy = true;
            this.error = '';
            try {
                await nxJsonFetch(urlWithId(this.urls.destroy, id), { method: 'DELETE' });
                this.items = this.items.filter((x) => x.id !== id);
                if (this.editingId === id) {
                    this.cancelEdit();
                }
            } catch (e) {
                this.error = this.msgError;
            } finally {
                this.busy = false;
            }
        },
    }));
}
