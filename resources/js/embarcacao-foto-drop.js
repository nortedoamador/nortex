/**
 * Drag-and-drop de fotos (través, popa ou outras) na ficha da embarcação.
 */
export function registerEmbarcacaoFotoDrop(Alpine) {
    Alpine.data('embarcacaoFotoDrop', (config) => ({
        action: config.action,
        csrf: config.csrf,
        msgNoImages: config.msgNoImages,
        msgSingleOnly: config.msgSingleOnly,
        msgOutrasRequired: config.msgOutrasRequired,
        msgForbidden: config.msgForbidden ?? 'Sem permissão para enviar fotos.',
        labelTraves: config.labelTraves,
        labelPopa: config.labelPopa,
        labelOutras: config.labelOutras,
        drag: false,
        uploading: false,
        files: [],
        tipo: 'traves',
        outrasDescricao: '',
        clientError: '',

        isImageFile(file) {
            if (!file || !file.type) {
                return /\.(jpe?g|png|webp)$/i.test(file.name || '');
            }
            return /^image\/(jpeg|png|webp)$/i.test(file.type);
        },

        onDragEnter(e) {
            e.preventDefault();
            this.drag = true;
        },

        onDragOver(e) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'copy';
            }
            this.drag = true;
        },

        onDragLeave(e) {
            const rel = e.relatedTarget;
            if (rel && e.currentTarget.contains(rel)) {
                return;
            }
            this.drag = false;
        },

        onDrop(e) {
            e.preventDefault();
            this.drag = false;
            this.clientError = '';
            const raw = Array.from(e.dataTransfer?.files || []);
            const list = raw.filter((f) => this.isImageFile(f));
            if (list.length === 0) {
                this.clientError = this.msgNoImages;
                return;
            }
            this.files = list;
        },

        onFileInputChange(e) {
            this.clientError = '';
            const raw = Array.from(e.target.files || []);
            const list = raw.filter((f) => this.isImageFile(f));
            this.files = list;
        },

        clearQueue() {
            this.files = [];
            this.clientError = '';
        },

        canSubmit() {
            return this.files.length > 0;
        },

        async submit() {
            this.clientError = '';
            if (!this.canSubmit() || this.uploading) {
                return;
            }
            if (this.tipo === 'outras') {
                if (!this.outrasDescricao.trim()) {
                    this.clientError = this.msgOutrasRequired;
                    return;
                }
            } else if (this.files.length > 1) {
                this.clientError = this.msgSingleOnly;
                return;
            }

            const fd = new FormData();
            fd.append('_token', this.csrf);
            if (this.tipo === 'outras') {
                this.files.forEach((f) => fd.append('fotos_outras[]', f));
                fd.append('fotos_outras_rotulo', this.outrasDescricao.trim());
            } else {
                const name = this.tipo === 'traves' ? 'foto_traves' : 'foto_popa';
                fd.append(name, this.files[0]);
            }

            this.uploading = true;
            try {
                // follow: após POST o Laravel devolve 302; com redirect:'manual' o browser
                // expõe opaqueredirect (status 0) e não conseguimos ler Location — falhava sempre.
                const res = await fetch(this.action, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    redirect: 'follow',
                    headers: {
                        Accept: 'application/json, text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (res.ok) {
                    window.location.reload();
                    return;
                }

                if (res.status === 419) {
                    this.clientError = 'Sessão expirada. Atualize a página.';
                    return;
                }

                if (res.status === 403) {
                    this.clientError = this.msgForbidden;
                    return;
                }

                if (res.status === 422) {
                    const j = await res.json().catch(() => null);
                    const errs = j?.errors ? Object.values(j.errors).flat() : [];
                    this.clientError =
                        errs.length > 0 ? errs.join(' ') : j?.message || 'Erro de validação.';
                    return;
                }

                this.clientError = 'Não foi possível enviar as fotos.';
            } catch {
                this.clientError = 'Não foi possível enviar as fotos.';
            } finally {
                this.uploading = false;
            }
        },
    }));
}
