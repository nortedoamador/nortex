/**
 * Modal «Novo processo» (passo 1 + 2): cria processo ao avançar, checklist + upload AJAX, concluir com observações.
 */
import { fireSwalTrocarAnexoLayout } from './processo-status-form-confirm';

export function registerNovoProcessoModal(Alpine) {
    Alpine.data('nxNovoProcessoModal', (cfg) => ({
        tipos: [],
        servicosPorCat: {},
        categoriaLabels: {},
        categoriaSel: cfg.categoriaSel ?? '',
        tipoSel: cfg.tipoSel ?? '',
        passo: cfg.passoInicial ?? 1,
        erroPasso1: '',
        erroPasso2: '',
        _prevModalOpen: false,
        processoId: null,
        checklistDocs: [],
        progresso: { enviados: 0, obrigatorios_ativos: 0, percentual: 0 },
        enviandoPasso1: false,
        enviandoAnexosDocId: null,
        removendoAnexoId: null,
        atualizandoDocStatusId: null,
        enviandoConcluir: false,
        msgs: cfg.msgs ?? {},
        chaSlugsExigemHabilitacao: Array.isArray(cfg.chaSlugsExigemHabilitacao)
            ? cfg.chaSlugsExigemHabilitacao
            : [],
        _skipNextOpenReset: !!cfg.skipResetOnFirstOpen,
        /** Passo 2: cópia reativa (evita x-text preso em «—»: leitura DOM não é dependência Alpine). */
        resumoClienteNome: '',
        resumoClienteDoc: '',
        resumoJurisdicao: '',
        /** Campos vindos da ficha da embarcação: bloqueados para evitar alteração por engano. */
        lockCamposPresetEmbarcacao: false,
        /** `embarcacao_id` quando o select está disabled (disabled não entra no POST). */
        presetEmbarcacaoIdLocked: '',

        limparCamposClienteModal() {
            const idEl = document.getElementById('modal_proc_cliente_id');
            const nomeEl = document.getElementById('modal_proc_interessado_nome');
            const habEl = document.getElementById('modal_proc_habilitacao_id');
            if (habEl) {
                habEl.value = '';
            }
            if (idEl) {
                idEl.value = '';
                delete idEl.dataset.clienteRouteKey;
            }
            if (nomeEl) {
                nomeEl.value = '';
            }
            const cpfInput = document.getElementById('modal_proc_cpf_interessado');
            if (cpfInput) {
                const root = cpfInput.closest('[x-data]');
                if (root && typeof window.Alpine !== 'undefined' && typeof window.Alpine.$data === 'function') {
                    const d = window.Alpine.$data(root);
                    if (d) {
                        d.q = '';
                        d.open = false;
                        d.filtered = [];
                        d.highlighted = -1;
                        d.panelStyle = '';
                    }
                }
            }
        },

        async resetModalFormState() {
            await this.descartarRascunho();
            this.passo = 1;
            this.erroPasso1 = '';
            this.erroPasso2 = '';
            this.enviandoPasso1 = false;
            this.enviandoConcluir = false;
            this.checklistDocs = [];
            this.progresso = { enviados: 0, obrigatorios_ativos: 0, percentual: 0 };
            this.categoriaSel = '';
            this.tipoSel = '';
            this.resumoClienteNome = '';
            this.resumoClienteDoc = '';
            this.resumoJurisdicao = '';
            this.lockCamposPresetEmbarcacao = false;
            this.presetEmbarcacaoIdLocked = '';
            this.limparCamposClienteModal();
            const jurEl = document.getElementById('modal_proc_jurisdicao');
            if (jurEl) {
                jurEl.value = '';
            }
            const habEl = document.getElementById('modal_proc_habilitacao_id');
            if (habEl) {
                habEl.value = '';
            }
            const obs = document.getElementById('modal_observacoes');
            if (obs) {
                obs.value = '';
            }
            document.querySelectorAll('input[type="file"][id^="nx-np-file-"]').forEach((el) => {
                el.value = '';
            });
            await this.$nextTick();
        },

        /** Pré-preenche modal após «Novo processo» na ficha da embarcação (categoria, cliente, embarcação). */
        async aplicarPresetLoja() {
            if (typeof window.Alpine === 'undefined' || typeof window.Alpine.store !== 'function') {
                return;
            }
            const st = window.Alpine.store('novoProcesso');
            const p = st.preset;
            if (!p || typeof p !== 'object') {
                return;
            }
            st.preset = null;

            const lockFromFicha = !!p.origemFichaEmbarcacao;

            const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

            if (p.categoria) {
                this.categoriaSel = p.categoria;
            }
            await this.$nextTick();
            await this.$nextTick();

            const clienteId =
                p.clienteId != null && p.clienteId !== '' ? String(p.clienteId) : '';

            const aplicarCliente = async () => {
                const cpfInputEl = document.getElementById('modal_proc_cpf_interessado');
                const cpfRoot = cpfInputEl?.closest('[x-data]');
                if (!clienteId || !cpfRoot || typeof window.Alpine.$data !== 'function') {
                    return false;
                }
                const d = window.Alpine.$data(cpfRoot);
                if (!d || !Array.isArray(d.items)) {
                    return false;
                }
                const item = d.items.find((i) => String(i.id) === clienteId);
                if (typeof d.pick === 'function' && item) {
                    d.pick(item);
                    return true;
                }
                const hid = document.getElementById('modal_proc_cliente_id');
                if (hid) {
                    hid.value = clienteId;
                    const rk = p.clienteRouteKey != null && p.clienteRouteKey !== '' ? String(p.clienteRouteKey) : '';
                    if (rk) {
                        hid.dataset.clienteRouteKey = rk;
                    } else {
                        delete hid.dataset.clienteRouteKey;
                    }
                }
                const nomeEl = document.getElementById('modal_proc_interessado_nome');
                if (nomeEl && p.clienteNome) {
                    nomeEl.value = String(p.clienteNome);
                }
                if (p.clienteDoc) {
                    d.q = String(p.clienteDoc);
                }
                hid?.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            };

            let okCliente = await aplicarCliente();
            if (clienteId && !okCliente) {
                await sleep(50);
                okCliente = await aplicarCliente();
            }

            await this.$nextTick();

            const embId =
                p.embarcacaoId != null && p.embarcacaoId !== '' ? String(p.embarcacaoId) : '';
            if (embId && clienteId) {
                const selEmb = document.getElementById('modal_proc_embarcacao_id');
                const embBlock = selEmb?.closest('[x-data]');
                if (embBlock && typeof window.Alpine.$data === 'function') {
                    const ed = window.Alpine.$data(embBlock);
                    if (ed && typeof ed.loadEmbarcacoes === 'function') {
                        await ed.loadEmbarcacoes();
                    }
                }
                await this.$nextTick();
                await this.$nextTick();
                const sel = document.getElementById('modal_proc_embarcacao_id');
                if (sel) {
                    sel.value = embId;
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            if (lockFromFicha) {
                this.lockCamposPresetEmbarcacao = true;
                if (embId) {
                    this.presetEmbarcacaoIdLocked = embId;
                }
            }
        },

        loadPayload() {
            try {
                const elT = document.getElementById('nx-np-json-tipos');
                const elS = document.getElementById('nx-np-json-servicos');
                const elL = document.getElementById('nx-np-json-labels');
                if (elT) {
                    this.tipos = JSON.parse(elT.textContent);
                }
                if (elS) {
                    this.servicosPorCat = JSON.parse(elS.textContent);
                }
                if (elL) {
                    this.categoriaLabels = JSON.parse(elL.textContent);
                }
            } catch (e) {
                this.tipos = [];
                this.servicosPorCat = {};
                this.categoriaLabels = {};
            }
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        },

        servicosFiltrados() {
            return this.servicosPorCat[this.categoriaSel] || [];
        },

        docsFiltrados() {
            if (!this.tipoSel) {
                return [];
            }
            const t = this.tipos.find((x) => String(x.id) === String(this.tipoSel));
            return t && t.documentos ? t.documentos : [];
        },

        /** Rótulo da categoria (alinha a `TipoProcessoCategoria` / `platform_tipo_servicos`). */
        nomeTipoServico() {
            return this.categoriaLabels[this.categoriaSel] || '\u2014';
        },

        /** Nome do `PlatformTipoProcesso` selecionado. */
        nomeTipoProcesso() {
            const s = this.servicosFiltrados().find((x) => String(x.id) === String(this.tipoSel));
            return s ? s.nome : '\u2014';
        },

        nomeClienteResumo() {
            const r = (this.resumoClienteNome || '').trim();
            if (r) {
                return r;
            }
            const el = document.getElementById('modal_proc_interessado_nome');
            return el && el.value ? el.value.trim() : '\u2014';
        },

        cpfClienteResumo() {
            const r = (this.resumoClienteDoc || '').trim();
            if (r) {
                return r;
            }
            const el = document.getElementById('modal_proc_cpf_interessado');
            return el && el.value ? el.value.trim() : '\u2014';
        },

        tipoExigeHabilitacaoCha() {
            const t = this.tipos.find((x) => String(x.id) === String(this.tipoSel));
            if (!t || t.categoria !== 'cha') {
                return false;
            }
            const slug = t.slug;
            return (this.chaSlugsExigemHabilitacao || []).includes(slug);
        },

        nomeJurisdicaoResumo() {
            const r = (this.resumoJurisdicao || '').trim();
            if (r) {
                return r;
            }
            const el = document.getElementById('modal_proc_jurisdicao');
            if (!el || !el.value) {
                return '\u2014';
            }
            const opt = el.options[el.selectedIndex];
            return opt && opt.text ? opt.text.trim() : '\u2014';
        },

        docsPendentesLista() {
            return this.checklistDocs.filter((d) => d.status === 'pendente');
        },

        validarPasso1() {
            this.erroPasso1 = '';
            const m = this.msgs;
            if (!this.categoriaSel) {
                this.erroPasso1 = m.selTipoServico ?? '';
                return false;
            }
            if (!this.tipoSel) {
                this.erroPasso1 = m.selTipoProcesso ?? '';
                return false;
            }
            const jurEl = document.getElementById('modal_proc_jurisdicao');
            if (!jurEl || !String(jurEl.value || '').trim()) {
                this.erroPasso1 = m.selJurisdicao ?? '';
                return false;
            }
            const idEl = document.getElementById('modal_proc_cliente_id');
            const cpfEl = document.getElementById('modal_proc_cpf_interessado');
            const nomeEl = document.getElementById('modal_proc_interessado_nome');
            if (!idEl || !String(idEl.value || '').trim()) {
                this.erroPasso1 = m.selClienteLista ?? '';
                return false;
            }
            if (!cpfEl || !String(cpfEl.value || '').trim()) {
                this.erroPasso1 = m.informeIdentificacao ?? '';
                return false;
            }
            if (!nomeEl || !String(nomeEl.value || '').trim()) {
                this.erroPasso1 = m.nomeCompleto ?? '';
                return false;
            }
            if (this.tipoExigeHabilitacaoCha()) {
                const hab = document.getElementById('modal_proc_habilitacao_id');
                if (!hab || !String(hab.value || '').trim()) {
                    this.erroPasso1 = m.selHabilitacaoCha ?? '';
                    return false;
                }
            }
            return true;
        },

        async descartarRascunho() {
            if (!this.processoId) {
                return;
            }
            const id = this.processoId;
            try {
                await fetch(`${cfg.base}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                });
            } catch (_) {
                /* noop */
            }
            this.processoId = null;
            this.checklistDocs = [];
            this.progresso = { enviados: 0, obrigatorios_ativos: 0, percentual: 0 };
        },

        aplicarProgresso(p) {
            if (!p || typeof p !== 'object') {
                return;
            }
            this.progresso = {
                enviados: p.enviados ?? 0,
                obrigatorios_ativos: p.obrigatorios_ativos ?? 0,
                percentual: p.percentual ?? 0,
            };
        },

        mergeDocumentosExtraFromResponse(data) {
            if (!Array.isArray(data.documentos_extra)) {
                return;
            }
            for (const d of data.documentos_extra) {
                this.mergeDocumentoNaChecklist(d);
            }
        },

        mergeDocumentoNaChecklist(doc) {
            if (!doc || doc.id == null) {
                return;
            }
            const rid = Number(doc.id);
            const row = this.checklistDocs.find((d) => Number(d.id) === rid);
            if (!row) {
                return;
            }
            if (doc.status != null) {
                row.status = doc.status;
            }
            if (doc.nome != null) {
                row.nome = doc.nome;
            }
            if (doc.obrigatorio != null) {
                row.obrigatorio = doc.obrigatorio;
            }
            if (doc.codigo != null) {
                row.codigo = doc.codigo;
            }
            if (doc.modelo_slug != null) {
                row.modelo_slug = doc.modelo_slug;
            }
            if (doc.declaracao_residencia_2g != null) {
                row.declaracao_residencia_2g = !!doc.declaracao_residencia_2g;
            }
            if (doc.url_declaracao_2g != null) {
                row.url_declaracao_2g = doc.url_declaracao_2g;
            }
            if (doc.declaracao_anexo_5h != null) {
                row.declaracao_anexo_5h = !!doc.declaracao_anexo_5h;
            }
            if (doc.url_declaracao_5h != null) {
                row.url_declaracao_5h = doc.url_declaracao_5h;
            }
            if (doc.declaracao_anexo_5d != null) {
                row.declaracao_anexo_5d = !!doc.declaracao_anexo_5d;
            }
            if (doc.url_declaracao_5d != null) {
                row.url_declaracao_5d = doc.url_declaracao_5d;
            }
            if (doc.declaracao_anexo_3d != null) {
                row.declaracao_anexo_3d = !!doc.declaracao_anexo_3d;
            }
            if (doc.url_declaracao_3d != null) {
                row.url_declaracao_3d = doc.url_declaracao_3d;
            }
            if (doc.preenchido_via_modelo != null) {
                row.preenchido_via_modelo = !!doc.preenchido_via_modelo;
            }
            if (Object.prototype.hasOwnProperty.call(doc, 'url_abrir_modelo')) {
                row.url_abrir_modelo = doc.url_abrir_modelo;
            }
            if (Object.prototype.hasOwnProperty.call(doc, 'url_visualizar_modelo')) {
                row.url_visualizar_modelo = doc.url_visualizar_modelo;
            }
            if (Object.prototype.hasOwnProperty.call(doc, 'data_validade_documento')) {
                row.data_validade_documento = doc.data_validade_documento;
            }
            row.anexos = Array.isArray(doc.anexos) ? doc.anexos : [];
        },

        async salvarValidadeCnh(row, valor) {
            const rid = Number(row?.id);
            if (!this.processoId || !rid || this.atualizandoDocStatusId !== null) {
                return;
            }
            const v = typeof valor === 'string' ? valor.trim() : '';
            const body = {
                status: row.status,
                data_validade_documento: v === '' ? null : v,
            };
            this.erroPasso2 = '';
            this.atualizandoDocStatusId = rid;
            const url = `${cfg.base}/${this.processoId}/documentos/${rid}`;
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg =
                        data.message ||
                        (data.errors && Object.values(data.errors).flat().join(' ')) ||
                        this.msgs.erroAtualizarDoc ||
                        'Erro';
                    this.erroPasso2 = msg;
                    this.atualizandoDocStatusId = null;
                    return;
                }
                if (data.documento?.id != null) {
                    this.mergeDocumentoNaChecklist(data.documento);
                }
                this.mergeDocumentosExtraFromResponse(data);
                this.aplicarProgresso(data.progresso);
            } catch (_) {
                this.erroPasso2 = this.msgs.erroRede ?? 'Erro de rede.';
            }
            this.atualizandoDocStatusId = null;
        },

        urlAbsoluta(u) {
            if (u == null || typeof u !== 'string') {
                return '#';
            }
            const t = u.trim();
            if (!t) {
                return '#';
            }
            if (/^https?:\/\//i.test(t)) {
                return t;
            }
            try {
                return new URL(t, window.location.origin).href;
            } catch (_) {
                return t;
            }
        },

        /** Códigos alinhados com App\Support\ChecklistDocumentoMultiplosAnexos (fotos embarcação / TIE). */
        permiteVariosAnexosFotos(row) {
            const c = row && typeof row.codigo === 'string' ? row.codigo : '';
            return (
                c === 'FOTOS_POPA_TRAVES' ||
                c === 'TIE_FOTOS_EMBARCACAO_LATERAL_POPA' ||
                c === 'TIE_FOTOS_MOTO_AQUATICA'
            );
        },

        async solicitarTrocarAnexoDigital(rowId) {
            const m = this.msgs.trocarAnexo || {};
            if (typeof window.Swal === 'undefined') {
                await this.patchDocumentoStatus(rowId, 'pendente');

                return;
            }
            const linhaProcesso = `${this.nomeTipoProcesso()} \u2014 ${this.nomeClienteResumo()}`;
            const fraseAviso = m.frase?.trim() || m.texto?.trim() || '';
            const r = await fireSwalTrocarAnexoLayout({
                titulo: m.titulo,
                linhaProcesso,
                fraseAviso: fraseAviso || undefined,
                textoPergunta: m.pergunta,
                confirmButtonText: m.confirmar,
                cancelButtonText: m.cancelar,
            });
            if (r.isConfirmed) {
                await this.patchDocumentoStatus(rowId, 'pendente');
            }
        },

        async patchDocumentoStatus(rowId, status, extra = {}) {
            const rid = Number(rowId);
            if (!this.processoId || !rid || this.atualizandoDocStatusId !== null) {
                return;
            }
            this.erroPasso2 = '';
            this.atualizandoDocStatusId = rid;
            const url = `${cfg.base}/${this.processoId}/documentos/${rid}`;
            const body = { status, ...extra };
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg =
                        data.message ||
                        (data.errors && Object.values(data.errors).flat().join(' ')) ||
                        this.msgs.erroAtualizarDoc ||
                        'Erro';
                    this.erroPasso2 = msg;
                    this.atualizandoDocStatusId = null;
                    return;
                }
                if (data.documento?.id != null) {
                    this.mergeDocumentoNaChecklist(data.documento);
                }
                this.mergeDocumentosExtraFromResponse(data);
                this.aplicarProgresso(data.progresso);
            } catch (_) {
                this.erroPasso2 = this.msgs.erroRede ?? 'Erro de rede.';
            }
            this.atualizandoDocStatusId = null;
        },

        async avancar() {
            if (!this.validarPasso1() || this.enviandoPasso1) {
                return;
            }
            this.enviandoPasso1 = true;
            this.erroPasso2 = '';
            const form = document.getElementById('nx-modal-novo-processo-form');
            if (!form) {
                this.enviandoPasso1 = false;
                return;
            }
            const fd = new FormData(form);
            fd.set('_novo_processo_passo', 'detalhes');
            try {
                const res = await fetch(cfg.store, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg =
                        data.message ||
                        (data.errors && Object.values(data.errors).flat().join(' ')) ||
                        this.msgs.erroCriar ||
                        'Erro';
                    this.erroPasso1 = msg;
                    this.enviandoPasso1 = false;
                    return;
                }
                const nomeEl = document.getElementById('modal_proc_interessado_nome');
                const cpfEl = document.getElementById('modal_proc_cpf_interessado');
                const jurSel = document.getElementById('modal_proc_jurisdicao');
                this.resumoClienteNome = nomeEl && nomeEl.value ? nomeEl.value.trim() : '';
                this.resumoClienteDoc = cpfEl && cpfEl.value ? cpfEl.value.trim() : '';
                if (jurSel && jurSel.value) {
                    const jo = jurSel.options[jurSel.selectedIndex];
                    this.resumoJurisdicao = jo && jo.text ? jo.text.trim() : String(jurSel.value);
                } else {
                    this.resumoJurisdicao = '';
                }
                this.processoId = data.processo?.id ?? null;
                this.checklistDocs = Array.isArray(data.documentos) ? data.documentos : [];
                this.aplicarProgresso(data.progresso);
                this.passo = 2;
            } catch (e) {
                this.erroPasso1 = this.msgs.erroRede ?? 'Erro de rede.';
            }
            this.enviandoPasso1 = false;
        },

        async voltarPasso1() {
            this.erroPasso1 = '';
            this.resumoClienteNome = '';
            this.resumoClienteDoc = '';
            this.resumoJurisdicao = '';
            await this.descartarRascunho();
            this.passo = 1;
        },

        async enviarAnexosParaDocumento(docId) {
            const id = Number(docId);
            if (!this.processoId || !id || this.enviandoAnexosDocId !== null) {
                return;
            }
            const input = document.getElementById(`nx-np-file-${id}`);
            if (!input || !input.files?.length) {
                this.erroPasso2 = this.msgs.selecioneArquivos ?? '';
                return;
            }
            this.erroPasso2 = '';
            this.enviandoAnexosDocId = id;
            const fd = new FormData();
            for (let i = 0; i < input.files.length; i++) {
                fd.append(`arquivos[${i}]`, input.files[i]);
            }
            const url = `${cfg.base}/${this.processoId}/documentos/${id}/anexos`;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg =
                        data.message ||
                        (data.errors && Object.values(data.errors).flat().join(' ')) ||
                        this.msgs.erroUpload ||
                        'Erro';
                    this.erroPasso2 = msg;
                    this.enviandoAnexosDocId = null;
                    return;
                }
                if (data.documento?.id != null) {
                    this.mergeDocumentoNaChecklist(data.documento);
                }
                this.mergeDocumentosExtraFromResponse(data);
                this.aplicarProgresso(data.progresso);
                input.value = '';
            } catch (_) {
                this.erroPasso2 = this.msgs.erroRede ?? 'Erro de rede.';
            }
            this.enviandoAnexosDocId = null;
        },

        async removerAnexo(rowId, anexoId) {
            const rid = Number(rowId);
            const aid = Number(anexoId);
            if (!this.processoId || !rid || !aid || this.removendoAnexoId !== null) {
                return;
            }
            this.erroPasso2 = '';
            this.removendoAnexoId = aid;
            const url = `${cfg.base}/${this.processoId}/documentos/${rid}/anexos/${aid}`;
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg =
                        data.message ||
                        (data.errors && Object.values(data.errors).flat().join(' ')) ||
                        this.msgs.erroRemoverAnexo ||
                        'Erro';
                    this.erroPasso2 = msg;
                    this.removendoAnexoId = null;
                    return;
                }
                if (data.documento?.id != null) {
                    this.mergeDocumentoNaChecklist(data.documento);
                }
                this.mergeDocumentosExtraFromResponse(data);
                this.aplicarProgresso(data.progresso);
            } catch (_) {
                this.erroPasso2 = this.msgs.erroRede ?? 'Erro de rede.';
            }
            this.removendoAnexoId = null;
        },

        async concluir() {
            if (!this.processoId || this.enviandoConcluir) {
                return;
            }
            this.enviandoConcluir = true;
            this.erroPasso2 = '';
            const obs = document.getElementById('modal_observacoes')?.value ?? '';
            try {
                const res = await fetch(`${cfg.base}/${this.processoId}/observacoes`, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    body: JSON.stringify({ observacoes: obs }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg =
                        data.message ||
                        (data.errors && Object.values(data.errors).flat().join(' ')) ||
                        this.msgs.erroSalvar ||
                        'Erro';
                    this.erroPasso2 = msg;
                    this.enviandoConcluir = false;
                    return;
                }
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
            } catch (_) {
                this.erroPasso2 = this.msgs.erroRede ?? 'Erro de rede.';
            }
            this.enviandoConcluir = false;
        },

        init() {
            this.loadPayload();
            this.$watch('$store.novoProcesso.open', async (open) => {
                if (!open && this._prevModalOpen) {
                    await this.descartarRascunho();
                }
                if (open && !this._prevModalOpen) {
                    if (this._skipNextOpenReset) {
                        this._skipNextOpenReset = false;
                    } else {
                        await this.resetModalFormState();
                    }
                    await this.aplicarPresetLoja();
                }
                this._prevModalOpen = open;
            });
            this.$watch('categoriaSel', () => {
                const list = this.servicosFiltrados();
                if (!list.some((s) => String(s.id) === String(this.tipoSel))) {
                    this.tipoSel = '';
                }
            });
        },
    }));
}
