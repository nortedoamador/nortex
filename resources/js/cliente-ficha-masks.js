/**
 * Máscaras para o formulário de ficha de cadastro do cliente (Brasil).
 */

function onlyDigits(str) {
    return (str || '').replace(/\D/g, '');
}

function formatCpf(digits) {
    const d = digits.slice(0, 11);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`;
    if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
    return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
}

function formatCnpj(digits) {
    const d = digits.slice(0, 14);
    if (d.length <= 2) return d;
    if (d.length <= 5) return `${d.slice(0, 2)}.${d.slice(2)}`;
    if (d.length <= 8) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5)}`;
    if (d.length <= 12) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8)}`;
    return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8, 12)}-${d.slice(12)}`;
}

function formatCep(digits) {
    const d = digits.slice(0, 8);
    if (d.length <= 5) return d;
    return `${d.slice(0, 5)}-${d.slice(5)}`;
}

/** Data BR: dd/mm/aaaa (8 dígitos) */
function formatDateBr(digits) {
    const d = digits.slice(0, 8);
    if (d.length <= 2) return d;
    if (d.length <= 4) return `${d.slice(0, 2)}/${d.slice(2)}`;
    return `${d.slice(0, 2)}/${d.slice(2, 4)}/${d.slice(4)}`;
}

/** Telefone fixo (10 dígitos) ou celular (11): (DD) NNNNN-NNNN ou (DD) NNNN-NNNN */
function formatPhoneBr(digits) {
    const d = digits.slice(0, 11);
    if (d.length === 0) return '';
    if (d.length <= 2) return `(${d}`;
    if (d.length <= 6) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
    if (d.length <= 10) return `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
    return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
}

/** RG ou número da CNH: letras, números, ponto, hífen, barra e X. */
function sanitizeRgCnh(value) {
    return value.replace(/[^\d.\-XxA-Za-z/]/g, '').slice(0, 40);
}

function bindInputMask(input, formatFn) {
    if (!input) return;

    const apply = () => {
        const formatted = formatFn(input.value);
        if (formatted !== input.value) {
            input.value = formatted;
        }
    };

    input.addEventListener('input', apply);
    input.addEventListener('blur', apply);
    apply();
}

function bindRgCnh(input) {
    if (!input) return;

    const apply = () => {
        const next = sanitizeRgCnh(input.value);
        if (next !== input.value) {
            input.value = next;
        }
    };

    input.addEventListener('input', apply);
    input.addEventListener('blur', apply);
    apply();
}

function bindCpfOuCnpj(root) {
    const input = root.querySelector('#cpf');
    if (!input) return;

    const tipo = () => root.querySelector('input[name="tipo_documento"]:checked')?.value || 'pf';
    const labelEl = root.querySelector('[data-doc-principal-label]');
    const nomeLabel = root.querySelector('[data-nome-label]');
    const nomeInput = root.querySelector('#nome');

    const setLabelAndPlaceholder = () => {
        const isCnpj = tipo() === 'pj';
        if (labelEl) {
            const text = isCnpj ? labelEl.dataset.labelCnpj : labelEl.dataset.labelCpf;
            if (text) {
                labelEl.innerHTML = `${text} <span class="text-red-600">*</span>`;
            }
        }
        const ph = isCnpj ? input.dataset.phCnpj : input.dataset.phCpf;
        if (ph) {
            input.setAttribute('placeholder', ph);
        }

        if (nomeLabel) {
            const n = isCnpj ? nomeLabel.dataset.labelPj : nomeLabel.dataset.labelPf;
            if (n) {
                nomeLabel.innerHTML = `${n} <span class="text-red-600">*</span>`;
            }
        }
        if (nomeInput) {
            const nph = isCnpj ? nomeInput.dataset.phPj : nomeInput.dataset.phPf;
            if (nph) {
                nomeInput.setAttribute('placeholder', nph);
            }
        }
    };

    const apply = () => {
        const d = onlyDigits(input.value);
        const formatted = tipo() === 'pj' ? formatCnpj(d) : formatCpf(d);
        if (formatted !== input.value) {
            input.value = formatted;
        }
        input.maxLength = tipo() === 'pj' ? 18 : 14;
        setLabelAndPlaceholder();
    };

    input.addEventListener('input', apply);
    input.addEventListener('blur', apply);
    root.querySelectorAll('input[name="tipo_documento"]').forEach((r) => {
        r.addEventListener('change', apply);
    });
    apply();
}

function titleCasePtBr(value) {
    const s = (value || '').trim().replace(/\s+/g, ' ');
    if (!s) return '';

    const lowerWords = new Set([
        'de', 'da', 'do', 'das', 'dos',
        'e',
        'a', 'o', 'as', 'os',
        'em', 'no', 'na', 'nos', 'nas',
        'por', 'per', 'para', 'pra',
        'com', 'sem',
        'ao', 'aos', 'à', 'às',
        'um', 'uma', 'uns', 'umas',
    ]);

    const parts = s.split(' ');
    return parts
        .map((w, idx) => {
            const raw = w;
            const plain = raw.toLocaleLowerCase('pt-BR');
            if (idx > 0 && lowerWords.has(plain)) {
                return plain;
            }
            // Mantém siglas bem comuns (LTDA, ME, EPP, SA, S/A) se vierem em maiúsculas
            if (/^[A-Z0-9./-]{2,}$/.test(raw) && raw === raw.toUpperCase()) {
                return raw;
            }
            return plain.charAt(0).toLocaleUpperCase('pt-BR') + plain.slice(1);
        })
        .join(' ');
}

function bindNomeTitleCase(root) {
    const input = root.querySelector('#nome');
    if (!input) return;

    const apply = () => {
        const next = titleCasePtBr(input.value);
        if (next && next !== input.value) {
            input.value = next;
        }
    };
    input.addEventListener('blur', apply);
    input.addEventListener('change', apply);
    apply();
}

/**
 * @param {ParentNode} root — normalmente o elemento <form>
 */
export function initClienteFichaMasks(root) {
    if (!root) return;

    bindCpfOuCnpj(root);
    bindNomeTitleCase(root);
    bindInputMask(root.querySelector('#cep'), (v) => formatCep(onlyDigits(v)));
    bindInputMask(root.querySelector('#telefone'), (v) => formatPhoneBr(onlyDigits(v)));
    bindInputMask(root.querySelector('#celular'), (v) => formatPhoneBr(onlyDigits(v)));
    bindRgCnh(root.querySelector('#documento_identidade_numero'));
    bindInputMask(root.querySelector('#data_emissao_rg'), (v) => formatDateBr(onlyDigits(v)));
    bindInputMask(root.querySelector('#validade_cnh'), (v) => formatDateBr(onlyDigits(v)));
    bindInputMask(root.querySelector('#data_nascimento'), (v) => formatDateBr(onlyDigits(v)));
}
