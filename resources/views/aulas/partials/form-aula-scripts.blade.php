@once
    <script>
        function nxFormatCpfBr(v) {
            const d = String(v ?? '').replace(/\D/g, '').slice(0, 11);
            if (d.length <= 3) return d;
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`;
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
        }

        function nxAulaNauticaForm(cfg) {
            const safe = (v) => (v == null ? '' : String(v));
            const onlyDigits = (v) => safe(v).replace(/\D/g, '');
            const buscarEscolaInstrutorUrl = cfg.buscarEscolaInstrutorCpfUrl || '';

            return {
                alunos: Array.isArray(cfg.initialAlunos) ? cfg.initialAlunos : [],
                instrutores: Array.isArray(cfg.initialInstrutores)
                    ? cfg.initialInstrutores.map((x) => ({
                        ...x,
                        programa_atestado: x.programa_atestado != null && x.programa_atestado !== '' ? String(x.programa_atestado) : 'ambos',
                    }))
                    : [],

                cpfQ: '',
                sugestões: [],
                open: false,
                highlighted: -1,
                panelStyle: '',

                cpfInstrutorQ: '',
                sugestõesInstrutor: [],
                openInstrutor: false,
                highlightedInstrutor: -1,
                panelStyleInstrutor: '',

                get cpfDigits() {
                    return onlyDigits(this.cpfQ);
                },

                get cpfInstrutorDigits() {
                    return onlyDigits(this.cpfInstrutorQ);
                },

                syncPanelPos() {
                    this.$nextTick(() => {
                        const el = this.$refs.cpfInput;
                        if (!el) return;
                        const r = el.getBoundingClientRect();
                        this.panelStyle = `position:fixed;top:${r.bottom + 6}px;left:${r.left}px;width:${r.width}px;z-index:9999`;
                    });
                },

                syncInstrutorPanelPos() {
                    this.$nextTick(() => {
                        const el = this.$refs.cpfInstrutorInput;
                        if (!el) return;
                        const r = el.getBoundingClientRect();
                        this.panelStyleInstrutor = `position:fixed;top:${r.bottom + 6}px;left:${r.left}px;width:${r.width}px;z-index:9999`;
                    });
                },

                onCpfInput() {
                    const digits = onlyDigits(this.cpfQ).slice(0, 11);
                    this.cpfQ = nxFormatCpfBr(digits);
                    clearTimeout(this._nxCpfSearchDebounce);
                    this._nxCpfSearchDebounce = setTimeout(() => this.buscarCpf(), 300);
                },

                onCpfInstrutorInput() {
                    const digits = onlyDigits(this.cpfInstrutorQ).slice(0, 11);
                    this.cpfInstrutorQ = nxFormatCpfBr(digits);
                    clearTimeout(this._nxInsCpfSearchDebounce);
                    this._nxInsCpfSearchDebounce = setTimeout(() => this.buscarInstrutorCpf(), 300);
                },

                async buscarCpf() {
                    const q = this.cpfDigits;
                    if (!q) {
                        this.sugestões = [];
                        this.open = false;
                        this.highlighted = -1;
                        this.panelStyle = '';
                        return;
                    }

                    const url = new URL(cfg.buscarCpfUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();
                    this.sugestões = Array.isArray(json.items) ? json.items : [];
                    this.open = this.sugestões.length > 0;
                    this.highlighted = this.open ? 0 : -1;
                    if (this.open) this.syncPanelPos();
                },

                async buscarInstrutorCpf() {
                    const q = this.cpfInstrutorDigits;
                    if (!buscarEscolaInstrutorUrl || !q) {
                        this.sugestõesInstrutor = [];
                        this.openInstrutor = false;
                        this.highlightedInstrutor = -1;
                        this.panelStyleInstrutor = '';
                        return;
                    }

                    const url = new URL(buscarEscolaInstrutorUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();
                    this.sugestõesInstrutor = Array.isArray(json.items) ? json.items : [];
                    this.openInstrutor = this.sugestõesInstrutor.length > 0;
                    this.highlightedInstrutor = this.openInstrutor ? 0 : -1;
                    if (this.openInstrutor) this.syncInstrutorPanelPos();
                },

                pick(it) {
                    if (!it || !it.id) return;
                    const exists = this.alunos.some((a) => String(a.id) === String(it.id));
                    if (!exists) {
                        this.alunos.push({ id: it.id, nome: it.nome, cpf: it.cpf });
                    }
                    this.cpfQ = '';
                    this.sugestões = [];
                    this.open = false;
                    this.highlighted = -1;
                    this.panelStyle = '';
                },

                pickInstrutor(it) {
                    if (!it || !it.id) return;
                    const exists = this.instrutores.some((a) => String(a.id) === String(it.id));
                    if (!exists) {
                        this.instrutores.push({
                            id: it.id,
                            nome: it.nome,
                            cpf: it.cpf,
                            cha: it.cha != null ? String(it.cha) : '',
                            programa_atestado: 'ambos',
                        });
                    }
                    this.cpfInstrutorQ = '';
                    this.sugestõesInstrutor = [];
                    this.openInstrutor = false;
                    this.highlightedInstrutor = -1;
                    this.panelStyleInstrutor = '';
                },

                removeAluno(id) {
                    this.alunos = this.alunos.filter((a) => String(a.id) !== String(id));
                },

                removeInstrutor(id) {
                    this.instrutores = this.instrutores.filter((a) => String(a.id) !== String(id));
                },

                onBlur() {
                    setTimeout(() => { this.open = false; this.panelStyle = ''; }, 150);
                },

                onInstrutorBlur() {
                    setTimeout(() => { this.openInstrutor = false; this.panelStyleInstrutor = ''; }, 150);
                },

                onKeydown(e) {
                    if (!this.open || !this.sugestões.length) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlighted = Math.min(this.highlighted + 1, this.sugestões.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlighted = Math.max(this.highlighted - 1, 0);
                    } else if (e.key === 'Enter' && this.highlighted >= 0) {
                        e.preventDefault();
                        this.pick(this.sugestões[this.highlighted]);
                    } else if (e.key === 'Escape') {
                        this.open = false;
                        this.panelStyle = '';
                    }
                },

                onInstrutorKeydown(e) {
                    if (!this.openInstrutor || !this.sugestõesInstrutor.length) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlightedInstrutor = Math.min(this.highlightedInstrutor + 1, this.sugestõesInstrutor.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlightedInstrutor = Math.max(this.highlightedInstrutor - 1, 0);
                    } else if (e.key === 'Enter' && this.highlightedInstrutor >= 0) {
                        e.preventDefault();
                        this.pickInstrutor(this.sugestõesInstrutor[this.highlightedInstrutor]);
                    } else if (e.key === 'Escape') {
                        this.openInstrutor = false;
                        this.panelStyleInstrutor = '';
                    }
                },

                openNovoAluno() {
                    const digits = this.cpfDigits;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'novo-aluno-aula' }));
                    window.dispatchEvent(new CustomEvent('nx-novo-aluno-prefill', { detail: { cpf: digits } }));
                },

                openNovoInstrutor() {
                    const digits = this.cpfInstrutorDigits;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'novo-cliente-instrutor-escola' }));
                    window.requestAnimationFrame(() => {
                        window.dispatchEvent(new CustomEvent('nx-novo-instrutor-prefill', { detail: { cpf: digits } }));
                    });
                },

                init() {
                    window.addEventListener('nx-escola-instrutor-associado', (e) => {
                        const row = e?.detail;
                        if (!row || row.id == null) return;
                        const exists = this.instrutores.some((x) => String(x.id) === String(row.id));
                        if (!exists) {
                            this.instrutores.push({
                                id: row.id,
                                nome: row.nome || '',
                                cpf: row.cpf || '',
                                cha: row.cha != null && row.cha !== '' ? String(row.cha) : '',
                                programa_atestado: row.programa_atestado != null && row.programa_atestado !== '' ? String(row.programa_atestado) : 'ambos',
                            });
                        }
                    });
                },
            };
        }

        function nxNovoAlunoModal() {
            return {
                loading: false,
                form: {
                    nome: '',
                    cpf: '',
                    documento_identidade_numero: '',
                    orgao_emissor: '',
                    data_emissao_rg: '',
                    data_nascimento: '',
                    telefone: '',
                    categoria_cnh: '',
                    endereco: '',
                    cidade: '',
                },

                init() {
                    window.addEventListener('nx-novo-aluno-prefill', (e) => {
                        const cpf = (e?.detail?.cpf || '').toString();
                        if (cpf) this.form.cpf = cpf;
                    });
                },

                async submit() {
                    if (this.loading) return;
                    this.loading = true;
                    try {
                        const res = await fetch(@js(route('alunos.modal-store')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                            body: JSON.stringify(this.form),
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok || !json.ok) {
                            alert(json.message || 'Falha ao salvar aluno.');
                            return;
                        }
                        const parent = this.$root.closest('form[x-data]') || document.querySelector('form[x-data]');
                        if (parent && window.Alpine && typeof window.Alpine.$data === 'function') {
                            const d = window.Alpine.$data(parent);
                            if (d && Array.isArray(d.alunos) && json.item) {
                                const exists = d.alunos.some((a) => String(a.id) === String(json.item.id));
                                if (!exists) d.alunos.push(json.item);
                            }
                        }
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'novo-aluno-aula' }));
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }

        function nxEscolaDiretorField(cfg) {
            const safe = (v) => (v == null ? '' : String(v));
            const onlyDigits = (v) => safe(v).replace(/\D/g, '');

            return {
                diretorId: cfg.initialId != null && cfg.initialId !== '' ? String(cfg.initialId) : '',
                diretorNome: cfg.initialNome || '',
                diretorCpf: cfg.initialCpf || '',
                cpfQ: '',
                sugestões: [],
                open: false,
                highlighted: -1,
                panelStyle: '',

                get cpfDigits() {
                    return onlyDigits(this.cpfQ);
                },

                syncPanelPos() {
                    this.$nextTick(() => {
                        const el = this.$refs.cpfInput;
                        if (!el) return;
                        const r = el.getBoundingClientRect();
                        this.panelStyle = `position:fixed;top:${r.bottom + 6}px;left:${r.left}px;width:${r.width}px;z-index:9999`;
                    });
                },

                onCpfInput() {
                    const digits = onlyDigits(this.cpfQ).slice(0, 11);
                    this.cpfQ = nxFormatCpfBr(digits);
                    clearTimeout(this._nxCpfSearchDebounce);
                    this._nxCpfSearchDebounce = setTimeout(() => this.buscarCpf(), 300);
                },

                async buscarCpf() {
                    const q = this.cpfDigits;
                    if (!q) {
                        this.sugestões = [];
                        this.open = false;
                        this.highlighted = -1;
                        this.panelStyle = '';
                        return;
                    }

                    const url = new URL(cfg.buscarCpfUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();
                    this.sugestões = Array.isArray(json.items) ? json.items : [];
                    this.open = this.sugestões.length > 0;
                    this.highlighted = this.open ? 0 : -1;
                    if (this.open) this.syncPanelPos();
                },

                pick(it) {
                    if (!it || !it.id) return;
                    this.diretorId = String(it.id);
                    this.diretorNome = it.nome || '';
                    this.diretorCpf = it.cpf || '';
                    this.cpfQ = '';
                    this.sugestões = [];
                    this.open = false;
                    this.highlighted = -1;
                    this.panelStyle = '';
                },

                onBlur() {
                    setTimeout(() => { this.open = false; this.panelStyle = ''; }, 150);
                },

                onKeydown(e) {
                    if (!this.open || !this.sugestões.length) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlighted = Math.min(this.highlighted + 1, this.sugestões.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlighted = Math.max(this.highlighted - 1, 0);
                    } else if (e.key === 'Enter' && this.highlighted >= 0) {
                        e.preventDefault();
                        this.pick(this.sugestões[this.highlighted]);
                    } else if (e.key === 'Escape') {
                        this.open = false;
                        this.panelStyle = '';
                    }
                },

                openNovoCliente() {
                    const digits = this.cpfDigits;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'novo-diretor-escola' }));
                    window.requestAnimationFrame(() => {
                        window.dispatchEvent(new CustomEvent('nx-novo-diretor-prefill', { detail: { cpf: digits } }));
                    });
                },

                clearDiretor() {
                    this.diretorId = '';
                    this.diretorNome = '';
                    this.diretorCpf = '';
                },

                resetToInitial() {
                    this.diretorId = cfg.initialId != null && cfg.initialId !== '' ? String(cfg.initialId) : '';
                    this.diretorNome = cfg.initialNome || '';
                    this.diretorCpf = cfg.initialCpf || '';
                    this.cpfQ = '';
                    this.sugestões = [];
                    this.open = false;
                    this.highlighted = -1;
                    this.panelStyle = '';
                },

                init() {
                    window.addEventListener('nx-escola-diretor-saved', (e) => {
                        const it = e?.detail;
                        if (!it || !it.id) return;
                        this.pick(it);
                    });
                },
            };
        }

        function nxNovoDiretorEscolaForm() {
            return {
                loading: false,

                init() {
                    window.addEventListener('open-modal', (e) => {
                        if (e.detail !== 'novo-diretor-escola') return;
                        const form = this.$root;
                        if (!(form instanceof HTMLFormElement)) return;
                        form.reset();
                        const pf = form.querySelector('input[name="tipo_documento"][value="pf"]');
                        if (pf) {
                            pf.checked = true;
                            pf.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        const cnh = form.querySelector('input[name="documento_identidade_tipo"][value="cnh"]');
                        if (cnh) {
                            cnh.checked = true;
                            cnh.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });

                    window.addEventListener('nx-novo-diretor-prefill', (e) => {
                        const form = this.$root;
                        if (!(form instanceof HTMLFormElement)) return;
                        const d = String(e?.detail?.cpf || '').replace(/\D/g, '');
                        const input = form.querySelector('#cpf');
                        if (!input || !d) return;
                        input.value = nxFormatCpfBr(d);
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                },

                async submitCliente() {
                    if (this.loading) return;
                    const form = this.$root;
                    if (!(form instanceof HTMLFormElement)) return;
                    this.loading = true;
                    try {
                        const fd = new FormData(form);
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: fd,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                        });
                        const json = await res.json().catch(() => ({}));
                        if (res.status === 422) {
                            const errs = json.errors || {};
                            const flat = Object.values(errs).flat().filter(Boolean);
                            alert(flat[0] || json.message || @js(__('Verifique os dados da ficha.')));
                            return;
                        }
                        if (!res.ok || !json.ok) {
                            alert(json.message || @js(__('Falha ao salvar cadastro.')));
                            return;
                        }
                        if (json.item) {
                            window.dispatchEvent(new CustomEvent('nx-escola-diretor-saved', { detail: json.item }));
                        }
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'novo-diretor-escola' }));
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }

        function nxNovoClienteInstrutorEscolaForm() {
            const nxChaDateToIsoParaApi = (raw) => {
                const s = String(raw ?? '').trim();
                if (!s) return '';
                const br = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                if (br) return `${br[3]}-${br[2]}-${br[1]}`;
                if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
                return '';
            };

            const readInstrutorCha = (form) => {
                const v = (sel) => {
                    const el = form.querySelector(sel);
                    if (!el) return '';
                    return String(el.value ?? '').trim();
                };
                return {
                    cha_numero: v('[data-instrutor-cha="numero"]'),
                    cha_categoria: v('[data-instrutor-cha="categoria"]'),
                    cha_data_emissao: nxChaDateToIsoParaApi(v('[data-instrutor-cha="data_emissao"]')),
                    cha_data_validade: nxChaDateToIsoParaApi(v('[data-instrutor-cha="data_validade"]')),
                    cha_jurisdicao: v('[data-instrutor-cha="jurisdicao"]'),
                };
            };

            const clearInstrutorCha = (form) => {
                form.querySelectorAll('[data-instrutor-cha]').forEach((el) => {
                    el.value = '';
                });
                const dv = form.querySelector('[data-instrutor-cha="data_validade"]');
                if (dv && dv.dataset) {
                    delete dv.dataset.nxManual;
                }
            };

            return {
                loading: false,

                init() {
                    window.addEventListener('open-modal', (e) => {
                        if (e.detail !== 'novo-cliente-instrutor-escola') return;
                        const form = this.$root;
                        if (!(form instanceof HTMLFormElement)) return;
                        form.reset();
                        clearInstrutorCha(form);
                        const pf = form.querySelector('input[name="tipo_documento"][value="pf"]');
                        if (pf) {
                            pf.checked = true;
                            pf.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        const cnh = form.querySelector('input[name="documento_identidade_tipo"][value="cnh"]');
                        if (cnh) {
                            cnh.checked = true;
                            cnh.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });

                    window.addEventListener('nx-novo-instrutor-prefill', (e) => {
                        const form = this.$root;
                        if (!(form instanceof HTMLFormElement)) return;
                        const d = String(e?.detail?.cpf || '').replace(/\D/g, '');
                        const input = form.querySelector('#cpf');
                        if (!input || !d) return;
                        input.value = nxFormatCpfBr(d);
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                },

                async submitCliente() {
                    if (this.loading) return;
                    const form = this.$root;
                    if (!(form instanceof HTMLFormElement)) return;
                    this.loading = true;
                    try {
                        const fd = new FormData(form);
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: fd,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                        });
                        const json = await res.json().catch(() => ({}));
                        if (res.status === 422) {
                            const errs = json.errors || {};
                            const flat = Object.values(errs).flat().filter(Boolean);
                            alert(flat[0] || json.message || @js(__('Verifique os dados da ficha.')));
                            return;
                        }
                        if (!res.ok || !json.ok) {
                            alert(json.message || @js(__('Falha ao salvar cadastro.')));
                            return;
                        }
                        if (json.item) {
                            const cha = readInstrutorCha(form);
                            const fd2 = new FormData();
                            fd2.append('_token', @js(csrf_token()));
                            fd2.append('cliente_id', String(json.item.id));
                            Object.entries(cha).forEach(([k, v]) => {
                                if (v) fd2.append(k, v);
                            });
                            const res2 = await fetch(@js(route('aulas.escola.instrutores.store')), {
                                method: 'POST',
                                body: fd2,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': @js(csrf_token()),
                                },
                            });
                            const j2 = await res2.json().catch(() => ({}));
                            if (res2.ok && j2.ok && j2.instrutor) {
                                window.dispatchEvent(new CustomEvent('nx-escola-instrutor-associado', { detail: j2.instrutor }));
                                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'novo-cliente-instrutor-escola' }));
                                if (window.Turbo && typeof window.Turbo.visit === 'function') {
                                    window.Turbo.visit(window.location.href);
                                } else {
                                    window.location.reload();
                                }
                                return;
                            }
                            window.dispatchEvent(new CustomEvent('nx-escola-instrutor-cliente-saved', { detail: { ...json.item, ...cha } }));
                        }
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'novo-cliente-instrutor-escola' }));
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }

        function nxEscolaInstrutorCpfField(cfg) {
            const safe = (v) => (v == null ? '' : String(v));
            const onlyDigits = (v) => safe(v).replace(/\D/g, '');

            return {
                pickedId: '',
                chaNumero: '',
                chaCategoria: '',
                chaDataEmissao: '',
                chaDataValidade: '',
                chaJurisdicao: '',
                cpfQ: '',
                sugestões: [],
                open: false,
                highlighted: -1,
                panelStyle: '',

                get cpfDigits() {
                    return onlyDigits(this.cpfQ);
                },

                syncPanelPos() {
                    this.$nextTick(() => {
                        const el = this.$refs.cpfInput;
                        if (!el) return;
                        const r = el.getBoundingClientRect();
                        this.panelStyle = `position:fixed;top:${r.bottom + 6}px;left:${r.left}px;width:${r.width}px;z-index:9999`;
                    });
                },

                onCpfInput() {
                    const digits = onlyDigits(this.cpfQ).slice(0, 11);
                    this.cpfQ = nxFormatCpfBr(digits);
                    clearTimeout(this._nxCpfSearchDebounce);
                    this._nxCpfSearchDebounce = setTimeout(() => this.buscarCpf(), 300);
                },

                enviarFormAssociar() {
                    const f = this.$refs.addForm;
                    if (!(f instanceof HTMLFormElement)) {
                        return;
                    }
                    if (typeof f.requestSubmit === 'function') {
                        f.requestSubmit();
                    } else {
                        f.submit();
                    }
                },

                async buscarCpf() {
                    const q = this.cpfDigits;
                    if (!q) {
                        this.sugestões = [];
                        this.open = false;
                        this.highlighted = -1;
                        this.panelStyle = '';
                        return;
                    }

                    const url = new URL(cfg.buscarCpfUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    const res = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    const json = await res.json().catch(() => ({}));
                    this.sugestões = Array.isArray(json.items) ? json.items : [];
                    this.open = this.sugestões.length > 0;
                    this.highlighted = this.open ? 0 : -1;
                    if (this.open) {
                        this.syncPanelPos();
                    }

                    if (q.length === 11 && this.sugestões.length === 1) {
                        const one = this.sugestões[0];
                        if (one && onlyDigits(one.cpf) === q) {
                            await this.$nextTick();
                            this.pick(one);
                        }
                    }
                },

                pick(it) {
                    if (!it || !it.id) return;
                    clearTimeout(this._blurTimeout);
                    const idStr = String(it.id);
                    this.pickedId = idStr;
                    this.chaNumero = '';
                    this.chaCategoria = '';
                    this.chaDataEmissao = '';
                    this.chaDataValidade = '';
                    this.chaJurisdicao = '';
                    this.cpfQ = '';
                    this.sugestões = [];
                    this.open = false;
                    this.highlighted = -1;
                    this.panelStyle = '';
                    this.$nextTick(() => {
                        const form = this.$refs.addForm;
                        if (form instanceof HTMLFormElement) {
                            const hid = form.querySelector('input[name="cliente_id"]');
                            if (hid instanceof HTMLInputElement) {
                                hid.value = idStr;
                            }
                        }
                        this.enviarFormAssociar();
                    });
                },

                onBlur() {
                    clearTimeout(this._blurTimeout);
                    this._blurTimeout = setTimeout(() => {
                        const panel = this.$refs.sugPanel;
                        const ae = document.activeElement;
                        if (panel && ae && panel.contains(ae)) {
                            return;
                        }
                        this.open = false;
                        this.highlighted = -1;
                        this.panelStyle = '';
                    }, 220);
                },

                onKeydown(e) {
                    if (e.key === 'Enter') {
                        const q = this.cpfDigits;
                        if (q.length === 11 && this.sugestões.length === 1 && onlyDigits(this.sugestões[0].cpf) === q) {
                            e.preventDefault();
                            this.pick(this.sugestões[0]);
                            return;
                        }
                    }
                    if (!this.open || !this.sugestões.length) {
                        return;
                    }
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlighted = Math.min(this.highlighted + 1, this.sugestões.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlighted = Math.max(this.highlighted - 1, 0);
                    } else if (e.key === 'Enter' && this.highlighted >= 0) {
                        e.preventDefault();
                        this.pick(this.sugestões[this.highlighted]);
                    } else if (e.key === 'Escape') {
                        this.open = false;
                        this.panelStyle = '';
                    }
                },

                openNovoCliente() {
                    const digits = this.cpfDigits;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'novo-cliente-instrutor-escola' }));
                    window.requestAnimationFrame(() => {
                        window.dispatchEvent(new CustomEvent('nx-novo-instrutor-prefill', { detail: { cpf: digits } }));
                    });
                },

                init() {
                    window.addEventListener('nx-escola-instrutor-cliente-saved', (e) => {
                        const it = e?.detail;
                        if (!it || !it.id) return;
                        this.pickedId = String(it.id);
                        this.chaNumero = it.cha_numero != null ? String(it.cha_numero) : '';
                        this.chaCategoria = it.cha_categoria != null ? String(it.cha_categoria) : '';
                        this.chaDataEmissao = it.cha_data_emissao != null ? String(it.cha_data_emissao) : '';
                        this.chaDataValidade = it.cha_data_validade != null ? String(it.cha_data_validade) : '';
                        this.chaJurisdicao = it.cha_jurisdicao != null ? String(it.cha_jurisdicao) : '';
                        this.$nextTick(() => this.enviarFormAssociar());
                    });
                },
            };
        }
    </script>
@endonce
