/**
 * Validação CPF/CNPJ no cliente (espelha App\Support\DocumentoBrasil).
 */

export function onlyDigits(str) {
    return (str || '').replace(/\D/g, '');
}

export function cpfValido(d) {
    const cpf = onlyDigits(d);
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    for (let t = 9; t < 11; t += 1) {
        let s = 0;
        for (let c = 0; c < t; c += 1) {
            s += parseInt(cpf[c], 10) * (t + 1 - c);
        }
        const v = ((10 * s) % 11) % 10;
        if (parseInt(cpf[t], 10) !== v) {
            return false;
        }
    }
    return true;
}

export function cnpjValido(d) {
    const cnpj = onlyDigits(d);
    if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
        return false;
    }
    let n = 0;
    let s = 5;
    for (let i = 0; i < 12; i += 1) {
        n += parseInt(cnpj[i], 10) * s;
        s -= 1;
        if (s < 2) {
            s = 9;
        }
    }
    const d1 = n % 11 < 2 ? 0 : 11 - (n % 11);
    if (parseInt(cnpj[12], 10) !== d1) {
        return false;
    }
    n = 0;
    s = 6;
    for (let i = 0; i < 13; i += 1) {
        n += parseInt(cnpj[i], 10) * s;
        s -= 1;
        if (s < 2) {
            s = 9;
        }
    }
    const d2 = n % 11 < 2 ? 0 : 11 - (n % 11);
    return parseInt(cnpj[13], 10) === d2;
}
