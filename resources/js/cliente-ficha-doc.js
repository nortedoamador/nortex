/**
 * Alterna opções de órgão emissor conforme RG ou CNH.
 */

function readOpts(root) {
    const el = root.querySelector('[data-orgao-emissor-opts="1"]');
    if (!el) return { rg: {}, cnh: {} };

    let rg = {};
    let cnh = {};
    try {
        rg = JSON.parse(el.dataset.optsRg || '{}');
    } catch {
        rg = {};
    }
    try {
        cnh = JSON.parse(el.dataset.optsCnh || '{}');
    } catch {
        cnh = {};
    }
    return { rg, cnh };
}

function selectedDocTipo(root) {
    const v = root.querySelector('input[name="documento_identidade_tipo"]:checked')?.value;
    return v === undefined || v === null ? 'cnh' : v;
}

function selectedUf(root) {
    return root.querySelector('#uf')?.value || '';
}

/**
 * @param {HTMLSelectElement} select
 * @param {Record<string,string>} opts
 * @param {string} keepValue
 */
function fillSelect(select, opts, keepValue) {
    const first = select.querySelector('option[value=""]');
    select.innerHTML = '';
    if (first) {
        select.appendChild(first);
    } else {
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = 'Selecione';
        ph.disabled = true;
        ph.hidden = true;
        ph.selected = true;
        select.appendChild(ph);
    }

    Object.entries(opts).forEach(([val, label]) => {
        const o = document.createElement('option');
        o.value = val;
        o.textContent = label;
        if (val === keepValue) {
            o.selected = true;
        }
        select.appendChild(o);
    });
}

function defaultOrgao(docTipo, uf) {
    if (!uf) return '';
    return docTipo === 'cnh' ? `DETRAN/${uf}` : `SSP/${uf}`;
}

/**
 * Textos do bloco de anexos (primeiro slot + “Outros”) conforme PF/PJ.
 *
 * @param {HTMLFormElement} root
 * @param {boolean} isPj
 */
function syncAnexosFichaUi(root, isPj) {
    root.querySelectorAll('[data-anexo-slot1-pf]').forEach((el) => {
        el.classList.toggle('hidden', !!isPj);
    });
    root.querySelectorAll('[data-anexo-slot1-pj]').forEach((el) => {
        el.classList.toggle('hidden', !isPj);
    });
    root.querySelectorAll('[data-anexo-intro-pf]').forEach((el) => {
        el.classList.toggle('hidden', !!isPj);
    });
    root.querySelectorAll('[data-anexo-intro-pj]').forEach((el) => {
        el.classList.toggle('hidden', !isPj);
    });

    const sel = root.querySelector('select[data-anexo-outro-preset="1"]');
    if (!(sel instanceof HTMLSelectElement)) {
        return;
    }

    for (const opt of sel.options) {
        const scope = (opt.getAttribute('data-scope') || 'pf').trim();
        const show = scope === 'both' || (scope === 'pf' && !isPj) || (scope === 'pj' && isPj);
        opt.hidden = !show;
        opt.disabled = !show;
    }

    const val = sel.value;
    const ok = Array.from(sel.options).some((o) => o.value === val && !o.disabled);
    if (!ok) {
        sel.value = '';
        sel.dispatchEvent(new Event('change', { bubbles: true }));
        sel.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

/**
 * @param {HTMLFormElement} root
 */
export function initClienteFichaDoc(root) {
    if (!root) return;

    const docNumeroLabel = root.querySelector('[data-doc-numero-label]');
    const docNumeroInput = root.querySelector('#documento_identidade_numero');
    const orgaoSel = root.querySelector('#orgao_emissor');
    const dataEmissao = root.querySelector('#data_emissao_rg');
    const validadeCnh = root.querySelector('#validade_cnh');
    const validadeWrap = root.querySelector('[data-validade-cnh-wrap="1"]');
    const emissaoWrap = root.querySelector('[data-emissao-doc-wrap="1"]');
    if (!orgaoSel) return;

    /** Coluna validade: classes Tailwind adicionadas só em JS podem não existir no CSS compilado; display/grid inline garante o efeito. */
    const setValidadeColumnVisible = (visible) => {
        if (validadeWrap) {
            if (visible) {
                validadeWrap.removeAttribute('hidden');
                validadeWrap.classList.remove('hidden');
                validadeWrap.style.removeProperty('display');
            } else {
                validadeWrap.setAttribute('hidden', 'hidden');
                validadeWrap.classList.add('hidden');
                validadeWrap.style.display = 'none';
            }
        }
        if (emissaoWrap) {
            if (visible) {
                emissaoWrap.style.removeProperty('grid-column');
                emissaoWrap.classList.remove('md:col-span-2');
            } else {
                emissaoWrap.style.removeProperty('grid-column');
                emissaoWrap.classList.add('md:col-span-2');
            }
        }
    };

    const { rg: optsRg, cnh: optsCnh } = readOpts(root);
    const blocos = Array.from(root.querySelectorAll('[data-doc-identidade-bloco="1"]'));
    const pfOnlyWraps = Array.from(root.querySelectorAll('[data-pf-only="1"]'));

    const syncPfOnlyVisibility = (isPj) => {
        pfOnlyWraps.forEach((wrap) => {
            if (isPj) {
                wrap.classList.add('hidden');
                wrap.querySelectorAll('input, select').forEach((el) => {
                    el.disabled = true;
                    el.removeAttribute('required');
                    if (el instanceof HTMLInputElement) {
                        el.value = '';
                    }
                    if (el instanceof HTMLSelectElement) {
                        el.value = '';
                    }
                });
            } else {
                wrap.classList.remove('hidden');
                wrap.querySelectorAll('input, select').forEach((el) => {
                    el.disabled = false;
                });
                wrap.querySelector('#nacionalidade')?.setAttribute('required', '');
                wrap.querySelector('#naturalidade')?.setAttribute('required', '');
            }
        });
    };

    const apply = (preferDefault) => {
        const docTipo = selectedDocTipo(root);
        const uf = selectedUf(root);
        const isCin = docTipo === 'cin';
        const opts = docTipo === 'cnh' ? optsCnh : optsRg;

        // PJ: esconde todo o bloco de identidade; PF: mostra.
        const tipoPrincipal = root.querySelector('input[name="tipo_documento"]:checked')?.value || 'pf';
        const hideAll = tipoPrincipal === 'pj';
        syncAnexosFichaUi(root, hideAll);
        blocos.forEach((el) => el.classList.toggle('hidden', hideAll));
        const docOrgRow = root.querySelector('[data-cliente-doc-org-row]');
        if (docOrgRow) {
            docOrgRow.classList.toggle('md:grid-cols-3', !hideAll);
        }
        syncPfOnlyVisibility(hideAll);
        if (hideAll) {
            // garante que nada fica “travado” ao voltar para PF
            if (docNumeroInput) docNumeroInput.disabled = false;
            if (orgaoSel) orgaoSel.disabled = false;
            if (dataEmissao) dataEmissao.disabled = false;
            if (validadeCnh instanceof HTMLInputElement) {
                validadeCnh.disabled = true;
                validadeCnh.readOnly = false;
                validadeCnh.value = '';
                validadeCnh.removeAttribute('placeholder');
            }
            setValidadeColumnVisible(false);
            return;
        }

        const setNumeroDisabled = (disabled) => {
            const toggleEl = (el) => {
                if (!el) return;
                el.disabled = disabled;
                el.toggleAttribute('aria-disabled', disabled);
                el.classList.toggle('opacity-60', disabled);
                el.classList.toggle('cursor-not-allowed', disabled);
                el.classList.toggle('bg-slate-100', disabled);
                el.classList.toggle('text-slate-500', disabled);
                el.classList.toggle('border-slate-200', disabled);
                el.classList.toggle('dark:bg-slate-800', disabled);
                el.classList.toggle('dark:text-slate-400', disabled);
                el.classList.toggle('dark:border-slate-700', disabled);
            };
            toggleEl(docNumeroInput);
        };

        if (docNumeroLabel) {
            const lbl = isCin
                ? docNumeroLabel.dataset.labelCin
                : docTipo === 'cnh'
                    ? docNumeroLabel.dataset.labelCnh
                    : docNumeroLabel.dataset.labelRg;
            if (lbl) {
                const star = docNumeroLabel.querySelector('span.text-red-600')?.outerHTML || ' <span class="text-red-600">*</span>';
                // CIN usa CPF como número, então não pede digitar, mas ainda existe “número do documento”
                docNumeroLabel.innerHTML = `${lbl}${star}`;
            }
        }
        if (docNumeroInput) {
            const ph = isCin
                ? docNumeroInput.dataset.phCin
                : docTipo === 'cnh'
                    ? docNumeroInput.dataset.phCnh
                    : docNumeroInput.dataset.phRg;
            if (ph) {
                docNumeroInput.setAttribute('placeholder', ph);
            }
        }

        // CIN: desabilita só o número (repete CPF); mantém órgão e data obrigatórios.
        if (isCin) {
            setNumeroDisabled(true);
            if (docNumeroInput) {
                docNumeroInput.value = root.querySelector('#cpf')?.value || '';
            }
        } else {
            setNumeroDisabled(false);
        }

        const current = orgaoSel.value || '';
        const def = defaultOrgao(docTipo, uf);
        const keep = Object.prototype.hasOwnProperty.call(opts, current) ? current : '';
        fillSelect(orgaoSel, opts, keep);

        if (preferDefault) {
            if (def && Object.prototype.hasOwnProperty.call(opts, def)) {
                orgaoSel.value = def;
            }
        } else if (!orgaoSel.value && def && Object.prototype.hasOwnProperty.call(opts, def)) {
            orgaoSel.value = def;
        }
        orgaoSel.dispatchEvent(new Event('change', { bubbles: true }));

        syncValidadeFromRules();
    };

    root.querySelectorAll('input[name="documento_identidade_tipo"]').forEach((r) => {
        r.addEventListener('change', () => apply(true));
    });

    root.querySelector('#uf')?.addEventListener('change', () => apply(false));
    root.querySelectorAll('input[name="tipo_documento"]').forEach((r) => {
        r.addEventListener('change', () => apply(false));
    });
    root.querySelector('#cpf')?.addEventListener('input', () => {
        if (selectedDocTipo(root) === 'cin' && docNumeroInput) {
            docNumeroInput.value = root.querySelector('#cpf')?.value || '';
        }
    });

    const addYearsIso = (isoDateStr, years) => {
        if (!isoDateStr || !/^\d{4}-\d{2}-\d{2}$/.test(isoDateStr)) return '';
        const [y, m, d] = isoDateStr.split('-').map(Number);
        const dt = new Date(Date.UTC(y, m - 1, d));
        if (Number.isNaN(dt.getTime())) return '';
        dt.setUTCFullYear(dt.getUTCFullYear() + years);
        const yy = dt.getUTCFullYear();
        const mm = String(dt.getUTCMonth() + 1).padStart(2, '0');
        const dd = String(dt.getUTCDate()).padStart(2, '0');
        return `${yy}-${mm}-${dd}`;
    };

    const brToIso = (br) => {
        const s = String(br || '').trim();
        const m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!m) return '';
        return `${m[3]}-${m[2]}-${m[1]}`;
    };

    const isoToBr = (iso) => {
        const s = String(iso || '').trim();
        const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!m) return '';
        return `${m[3]}/${m[2]}/${m[1]}`;
    };

    const parseIsoUtc = (dateStr) => {
        const raw = String(dateStr || '').trim();
        const iso = raw.includes('/') ? brToIso(raw) : raw;
        if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return null;
        const [y, m, d] = iso.split('-').map(Number);
        const dt = new Date(Date.UTC(y, m - 1, d));
        if (Number.isNaN(dt.getTime())) return null;
        return dt;
    };

    const calcAgeAt = (birthIso, atIso) => {
        const b = parseIsoUtc(birthIso);
        const a = parseIsoUtc(atIso);
        if (!b || !a) return null;
        let age = a.getUTCFullYear() - b.getUTCFullYear();
        const m = a.getUTCMonth() - b.getUTCMonth();
        if (m < 0 || (m === 0 && a.getUTCDate() < b.getUTCDate())) {
            age -= 1;
        }
        return age;
    };

    const setValidadeState = ({ disabled, readOnly, value, placeholder }) => {
        if (!validadeCnh || !(validadeCnh instanceof HTMLInputElement)) return;
        validadeCnh.disabled = !!disabled;
        validadeCnh.readOnly = !!readOnly;
        if (typeof placeholder === 'string') {
            validadeCnh.setAttribute('placeholder', placeholder);
        }
        if (value !== undefined) {
            validadeCnh.value = value;
            validadeCnh.dispatchEvent(new Event('input', { bubbles: true }));
            validadeCnh.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const syncValidadeFromRules = () => {
        if (!dataEmissao || !(dataEmissao instanceof HTMLInputElement)) return;
        if (!validadeCnh || !(validadeCnh instanceof HTMLInputElement)) return;

        const docTipo = selectedDocTipo(root);
        const emBr = String(dataEmissao.value || '').trim();
        const em = brToIso(emBr);
        const nascEl = root.querySelector('#data_nascimento');
        const nascBr = nascEl && nascEl instanceof HTMLInputElement ? String(nascEl.value || '').trim() : '';
        const nasc = brToIso(nascBr);

        // RG não tem validade: oculta coluna e não envia o campo
        if (docTipo === 'rg') {
            setValidadeColumnVisible(false);
            setValidadeState({ disabled: true, readOnly: false, value: '', placeholder: '' });
            return;
        }

        setValidadeColumnVisible(true);

        // Para CNH/CIN precisamos de data de emissão e nascimento completas (dd/mm/aaaa) para calcular.
        if (!em || !nasc) {
            setValidadeState({ disabled: false, readOnly: true, value: '', placeholder: '' });
            return;
        }

        const age = calcAgeAt(nasc, em);
        if (age === null) {
            setValidadeState({ disabled: false, readOnly: true, value: '', placeholder: '' });
            return;
        }

        if (docTipo === 'cnh') {
            const years = age <= 49 ? 10 : age <= 69 ? 5 : 3;
            const next = addYearsIso(em, years);
            if (!next) {
                setValidadeState({ disabled: false, readOnly: true, value: '', placeholder: '' });
                return;
            }
            setValidadeState({ disabled: false, readOnly: true, value: isoToBr(next), placeholder: '' });
            return;
        }

        if (docTipo === 'cin') {
            if (age >= 60) {
                // vitalícia (não envia)
                setValidadeState({ disabled: true, readOnly: false, value: '', placeholder: 'Vitalícia' });
                return;
            }
            const years = age < 12 ? 5 : 10;
            const next = addYearsIso(em, years);
            if (!next) {
                setValidadeState({ disabled: false, readOnly: true, value: '', placeholder: '' });
                return;
            }
            setValidadeState({ disabled: false, readOnly: true, value: isoToBr(next), placeholder: '' });
            return;
        }

        // fallback: sem cálculo automático
        setValidadeState({ disabled: false, readOnly: true, value: '', placeholder: '' });
    };

    if (dataEmissao instanceof HTMLInputElement) {
        dataEmissao.addEventListener('change', syncValidadeFromRules);
        dataEmissao.addEventListener('input', syncValidadeFromRules);
    }
    const nascEl = root.querySelector('#data_nascimento');
    if (nascEl instanceof HTMLInputElement) {
        nascEl.addEventListener('change', syncValidadeFromRules);
        nascEl.addEventListener('input', syncValidadeFromRules);
    }
    root.querySelectorAll('input[name="documento_identidade_tipo"]').forEach((r) => {
        r.addEventListener('change', () => {
            syncValidadeFromRules();
        });
    });
    // Se já vier preenchido (ex.: edição), tenta preencher.
    syncValidadeFromRules();

    apply(false);
}

/**
 * Marca o tipo de documento (rg | cnh | cin) e dispara o mesmo fluxo do utilizador.
 *
 * @param {HTMLFormElement} form
 * @param {'rg'|'cnh'|'cin'} tipo
 */
export function setDocumentoIdentidadeTipo(form, tipo) {
    if (!form) return;
    const r = form.querySelector(`input[name="documento_identidade_tipo"][value="${tipo}"]`);
    if (!r) return;
    r.checked = true;
    r.dispatchEvent(new Event('change', { bubbles: true }));
}

