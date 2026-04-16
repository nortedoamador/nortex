/**
 * IBGE (municípios), ViaCEP e sugestões a partir do CPF na ficha do cliente.
 */

import { onlyDigits, cpfValido } from './documento-brasil-client';

/** Nono dígito do CPF → UF representativa (regra histórica RF). */
function ufDoNonoDigitoCpf(d) {
    const n = parseInt(d[8], 10);
    const map = { 0: 'RS', 1: 'DF', 2: 'AM', 3: 'CE', 4: 'PE', 5: 'BA', 6: 'MG', 7: 'RJ', 8: 'SP' };
    return map[n] ?? null;
}

/**
 * @param {HTMLFormElement} root
 */
export function initClienteFichaGeo(root) {
    if (!root) {
        return;
    }

    let capitais = {};
    try {
        capitais = JSON.parse(root.dataset.capitais || '{}');
    } catch {
        capitais = {};
    }

    const msgMun = root.dataset.msgSelecioneMunicipio || 'Selecione o município';

    /** @type {Map<string, string[]>} */
    const ibgeMunicipiosCache = new Map();

    const idCep = root.dataset.geoCep || 'cep';
    const idEndereco = root.dataset.geoEndereco || 'endereco';
    const idBairro = root.dataset.geoBairro || 'bairro';
    const idComplemento = root.dataset.geoComplemento || '';

    const ufSel = root.querySelector('#uf');
    const cidadeSel = root.querySelector('#cidade');
    const naturalidadeSel = root.querySelector('#naturalidade');
    const cpfInput = root.querySelector('#cpf');
    const cepInput = root.querySelector(`#${idCep}`);
    const enderecoInput = root.querySelector(`#${idEndereco}`);
    const orgaoSel = root.querySelector('#orgao_emissor');
    const nacSel = root.querySelector('#nacionalidade');
    /** @type {HTMLSelectElement|HTMLInputElement|null} */
    const bairroSel = root.querySelector(`#${idBairro}`);
    const complementoInput = idComplemento ? root.querySelector(`#${idComplemento}`) : null;
    const bairroOutro = root.querySelector('#bairro_outro');
    const bairroIsSelect = bairroSel instanceof HTMLSelectElement;
    const docIdTipo = () => root.querySelector('input[name="documento_identidade_tipo"]:checked')?.value || 'cnh';

    function tipoDocumento() {
        return root.querySelector('input[name="tipo_documento"]:checked')?.value || 'pf';
    }

    async function fetchMunicipiosNomes(uf) {
        if (!uf) {
            return [];
        }
        if (ibgeMunicipiosCache.has(uf)) {
            return ibgeMunicipiosCache.get(uf);
        }
        try {
            const r = await fetch(
                `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${encodeURIComponent(uf)}/municipios`,
            );
            if (!r.ok) {
                return [];
            }
            const data = await r.json();
            const nomes = data.map((m) => m.nome).sort((a, b) => a.localeCompare(b, 'pt-BR'));
            ibgeMunicipiosCache.set(uf, nomes);
            return nomes;
        } catch {
            return [];
        }
    }

    /**
     * @param {HTMLSelectElement|null} select
     * @param {string[]} nomes
     * @param {string} [preset]
     */
    function fillMunicipioSelect(select, nomes, preset) {
        if (!select) {
            return;
        }
        const keep = preset !== undefined && preset !== null ? preset : select.value;
        select.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = msgMun;
        ph.disabled = true;
        ph.hidden = true;
        if (!keep) {
            ph.selected = true;
        }
        select.appendChild(ph);
        let ok = false;
        nomes.forEach((nome) => {
            const o = document.createElement('option');
            o.value = nome;
            o.textContent = nome;
            if (nome === keep) {
                o.selected = true;
                ok = true;
            }
            select.appendChild(o);
        });
        if (keep && !ok) {
            const o = document.createElement('option');
            o.value = keep;
            o.textContent = keep;
            o.selected = true;
            select.appendChild(o);
        }
    }

    async function refreshMunicipios(uf, presetCidade, presetNaturalidade) {
        const nomes = await fetchMunicipiosNomes(uf);
        const curC = presetCidade !== undefined ? presetCidade : cidadeSel?.value || '';
        const curN = presetNaturalidade !== undefined ? presetNaturalidade : naturalidadeSel?.value || '';
        fillMunicipioSelect(cidadeSel, nomes, curC);
        fillMunicipioSelect(naturalidadeSel, nomes, curN);
    }

    async function sugerePorCpf() {
        if (tipoDocumento() !== 'pf' || !cpfInput) {
            return;
        }
        const d = onlyDigits(cpfInput.value);
        if (d.length !== 11 || !cpfValido(d)) {
            return;
        }
        const dig = parseInt(d[8], 10);
        if (dig !== 9 && nacSel) {
            const opt = Array.from(nacSel.options).find((o) => o.value === 'Brasileira');
            if (opt) {
                nacSel.value = 'Brasileira';
            }
        }
        const uf = ufDoNonoDigitoCpf(d);
        if (!uf || !ufSel) {
            return;
        }
        ufSel.value = uf;
        const orgao = docIdTipo() === 'cnh' ? `DETRAN/${uf}` : `SSP/${uf}`;
        if (orgaoSel && Array.from(orgaoSel.options).some((o) => o.value === orgao)) {
            orgaoSel.value = orgao;
        }
        const cap = capitais[uf] || '';
        await refreshMunicipios(uf, cap, cap);
    }

    function setBairroOpcao(label) {
        if (!bairroSel || !label) {
            return;
        }
        const v = String(label).trim();
        if (!v) {
            return;
        }
        if (bairroSel instanceof HTMLInputElement) {
            bairroSel.value = v;
            return;
        }
        const sel = bairroSel;
        for (let i = 0; i < sel.options.length; i += 1) {
            if (sel.options[i].value === v) {
                sel.selectedIndex = i;
                if (bairroOutro) {
                    bairroOutro.classList.add('hidden');
                    bairroOutro.value = '';
                }
                return;
            }
        }
        const outroOpt = Array.from(sel.options).find((o) => o.value === '__outro');
        const opt = document.createElement('option');
        opt.value = v;
        opt.textContent = v;
        if (outroOpt) {
            sel.insertBefore(opt, outroOpt);
        } else {
            sel.appendChild(opt);
        }
        opt.selected = true;
        if (bairroOutro) {
            bairroOutro.classList.add('hidden');
            bairroOutro.value = '';
        }
    }

    function cleanupBairroHidden() {
        root.querySelectorAll('input[data-bairro-hidden="1"]').forEach((el) => el.remove());
        if (bairroIsSelect && bairroSel && !bairroSel.hasAttribute('name')) {
            bairroSel.setAttribute('name', 'bairro');
        }
    }

    function resetBairroOptions() {
        if (!bairroIsSelect || !bairroSel) {
            return;
        }
        const keep = bairroSel.value;
        bairroSel.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = 'Selecione o bairro';
        if (!keep) {
            ph.selected = true;
        }
        bairroSel.appendChild(ph);
        const outro = document.createElement('option');
        outro.value = '__outro';
        outro.textContent = 'Outro bairro (digite)';
        bairroSel.appendChild(outro);
        if (keep && keep !== '__outro') {
            // mantém valor antigo como opção transitória (ex.: veio do CEP)
            const opt = document.createElement('option');
            opt.value = keep;
            opt.textContent = keep;
            opt.selected = true;
            bairroSel.insertBefore(opt, outro);
        }
        if (bairroOutro) {
            bairroOutro.classList.add('hidden');
            bairroOutro.value = '';
        }
    }

    cpfInput?.addEventListener('blur', () => {
        void sugerePorCpf();
    });

    ufSel?.addEventListener('change', () => {
        cleanupBairroHidden();
        void refreshMunicipios(ufSel.value, '', '');
    });

    cidadeSel?.addEventListener('change', () => {
        cleanupBairroHidden();
        resetBairroOptions();
    });

    cepInput?.addEventListener('blur', () => {
        void (async () => {
            const cep = onlyDigits(cepInput.value);
            if (cep.length !== 8) {
                return;
            }
            try {
                const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await r.json();
                if (data.erro) {
                    return;
                }
                if (data.uf && ufSel) {
                    ufSel.value = data.uf;
                }
                const loc = data.localidade || '';
                await refreshMunicipios(data.uf, loc, naturalidadeSel?.value || '');
                if (enderecoInput && data.logradouro) {
                    enderecoInput.value = data.logradouro;
                }
                if (complementoInput && data.complemento) {
                    const c = String(data.complemento).trim();
                    if (c && !String(complementoInput.value || '').trim()) {
                        complementoInput.value = c;
                    }
                }
                setBairroOpcao(data.bairro);
            } catch {
                /* rede / CORS */
            }
        })();
    });

    if (bairroIsSelect) {
        bairroSel.addEventListener('change', () => {
            cleanupBairroHidden();
            if (!bairroOutro || !bairroSel) {
                return;
            }
            if (bairroSel.value === '__outro') {
                bairroOutro.classList.remove('hidden');
                bairroOutro.focus();
            } else {
                bairroOutro.classList.add('hidden');
                bairroOutro.value = '';
            }
        });
    }

    root.addEventListener('submit', (e) => {
        if (!bairroIsSelect || !bairroSel || bairroSel.value !== '__outro') {
            return;
        }
        const t = (bairroOutro?.value || '').trim();
        if (!t) {
            e.preventDefault();
            return;
        }
        cleanupBairroHidden();
        bairroSel.removeAttribute('name');
        const h = document.createElement('input');
        h.type = 'hidden';
        h.name = 'bairro';
        h.value = t;
        h.setAttribute('data-bairro-hidden', '1');
        root.appendChild(h);
    });

    if (ufSel?.value) {
        void refreshMunicipios(ufSel.value, cidadeSel?.value || '', naturalidadeSel?.value || '');
    }
}
