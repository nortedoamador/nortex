import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/swal-pendencias.css';
import '../css/swal-excluir-processo.css';

const ATTR_SUBMIT_ON_CHANGE = 'data-nx-status-submit-on-change';
const INPUT_CIENCIA = 'confirmar_ciencia_pendencias_documentais';

const TITULO_SWAL_PADRAO = 'Processo com pendências';
const TEXTO_SEC_PADRAO = 'Deseja realmente alterar o status mesmo assim?';
const FRASE_PENDENTES_PADRAO = 'Há documentos obrigatórios pendentes no checklist.';

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

const ICON_AVISO_SVG = `<svg class="nx-swal-pendencias-tri" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`;

const ICON_DOC_SVG = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.5 14.5h3M12 11v3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`;

const ICON_LIXEIRA_SVG = `<svg class="nx-swal-excluir-lixeira" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>`;

/** Ícone no círculo do cabeçalho — mesmo estilo da lixeira (troca de anexo). */
const ICON_UPLOAD_TROCAR_ANEXO_SVG = `<svg class="nx-swal-excluir-lixeira" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/></svg>`;

const TITULO_EXCLUIR_PADRAO = 'Excluir processo?';
const FRASE_AVISO_EXCLUIR_PADRAO = 'Os anexos enviados serão removidos. Esta ação não pode ser desfeita.';
const PERGUNTA_EXCLUIR_PADRAO = 'Deseja realmente excluir este processo?';
const BTN_NAO_EXCLUIR_PADRAO = 'Não, desistir';
const BTN_SIM_EXCLUIR_PADRAO = 'Sim, excluir';

const TITULO_TROCAR_ANEXO_PADRAO = 'Trocar anexo?';
const FRASE_AVISO_TROCAR_ANEXO_PADRAO =
    'O ficheiro enviado anteriormente será eliminado ao substituir por um novo anexo.';
const PERGUNTA_TROCAR_ANEXO_PADRAO = 'Deseja realmente trocar o anexo?';
const BTN_NAO_TROCAR_ANEXO_PADRAO = 'Não, cancelar';
const BTN_SIM_TROCAR_ANEXO_PADRAO = 'Sim, trocar';

/**
 * @param {object} p
 * @param {string} p.titulo
 * @param {string} p.linhaProcesso
 * @param {string} p.fraseAviso
 * @param {string} p.textoPergunta
 * @param {string} [p.headerIconSvg]
 */
function buildExcluirStyleDialogHtml({ titulo, linhaProcesso, fraseAviso, textoPergunta, headerIconSvg }) {
    const icon = headerIconSvg ?? ICON_LIXEIRA_SVG;
    const t = escapeHtml(titulo);
    const linha = escapeHtml(linhaProcesso);
    const frase = escapeHtml(fraseAviso);
    const pergunta = escapeHtml(textoPergunta);
    return `
<div class="nx-swal-excluir-inner">
  <div class="nx-swal-excluir-header">
    <div class="nx-swal-excluir-icon-circle">${icon}</div>
    <h2 class="nx-swal-excluir-title">${t}</h2>
  </div>
  <div class="nx-swal-excluir-main">
    <p class="nx-swal-excluir-sub">${linha}</p>
    <div class="nx-swal-excluir-box">
      <span class="nx-swal-excluir-box-icon">${ICON_DOC_SVG}</span>
      <div class="nx-swal-excluir-box-text">
        <p class="nx-swal-excluir-box-strong">${frase}</p>
      </div>
    </div>
    <p class="nx-swal-excluir-pergunta">${pergunta}</p>
  </div>
</div>`;
}

/**
 * @param {object} opts
 * @param {string} opts.titulo
 * @param {string} opts.linhaProcesso
 * @param {string} opts.fraseAviso
 * @param {string} opts.textoPergunta
 * @param {string} [opts.confirmButtonText]
 * @param {string} [opts.cancelButtonText]
 */
export function fireSwalExcluirProcessoLayout(opts) {
    const titulo = opts.titulo?.trim() || TITULO_EXCLUIR_PADRAO;
    const linhaProcesso = opts.linhaProcesso?.trim() || '—';
    const fraseAviso = opts.fraseAviso?.trim() || FRASE_AVISO_EXCLUIR_PADRAO;
    const textoPergunta = opts.textoPergunta?.trim() || PERGUNTA_EXCLUIR_PADRAO;
    const confirmButtonText = opts.confirmButtonText?.trim() || BTN_SIM_EXCLUIR_PADRAO;
    const cancelButtonText = opts.cancelButtonText?.trim() || BTN_NAO_EXCLUIR_PADRAO;

    return Swal.fire({
        title: false,
        icon: false,
        html: buildExcluirStyleDialogHtml({ titulo, linhaProcesso, fraseAviso, textoPergunta }),
        showCloseButton: true,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        reverseButtons: true,
        focusCancel: true,
        buttonsStyling: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'nx-swal-excluir-popup',
            actions: 'nx-swal-pendencias-actions',
            confirmButton: 'nx-swal-pendencias-btn-sim',
            cancelButton: 'nx-swal-pendencias-btn-nao',
        },
        background: '#ffffff',
        color: '#0f172a',
        width: 420,
        padding: 0,
    });
}

/**
 * Mesmo layout visual que «Excluir processo» (cabeçalho rosa, caixa cinza, botões).
 * @param {object} opts
 * @param {string} opts.titulo
 * @param {string} opts.linhaProcesso
 * @param {string} opts.fraseAviso
 * @param {string} opts.textoPergunta
 * @param {string} [opts.confirmButtonText]
 * @param {string} [opts.cancelButtonText]
 */
export function fireSwalTrocarAnexoLayout(opts) {
    const titulo = opts.titulo?.trim() || TITULO_TROCAR_ANEXO_PADRAO;
    const linhaProcesso = opts.linhaProcesso?.trim() || '—';
    const fraseAviso = opts.fraseAviso?.trim() || FRASE_AVISO_TROCAR_ANEXO_PADRAO;
    const textoPergunta = opts.textoPergunta?.trim() || PERGUNTA_TROCAR_ANEXO_PADRAO;
    const confirmButtonText = opts.confirmButtonText?.trim() || BTN_SIM_TROCAR_ANEXO_PADRAO;
    const cancelButtonText = opts.cancelButtonText?.trim() || BTN_NAO_TROCAR_ANEXO_PADRAO;

    return Swal.fire({
        title: false,
        icon: false,
        html: buildExcluirStyleDialogHtml({
            titulo,
            linhaProcesso,
            fraseAviso,
            textoPergunta,
            headerIconSvg: ICON_UPLOAD_TROCAR_ANEXO_SVG,
        }),
        showCloseButton: true,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        reverseButtons: true,
        focusCancel: true,
        buttonsStyling: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'nx-swal-excluir-popup',
            actions: 'nx-swal-pendencias-actions',
            confirmButton: 'nx-swal-pendencias-btn-sim',
            cancelButton: 'nx-swal-pendencias-btn-nao',
        },
        background: '#ffffff',
        color: '#0f172a',
        width: 420,
        padding: 0,
    });
}

function buildPendenciasDialogHtml({ titulo, linhaProcesso, frasePendentes, textoSecundario }) {
    const t = escapeHtml(titulo);
    const linha = escapeHtml(linhaProcesso);
    const frase = escapeHtml(frasePendentes);
    const sec = escapeHtml(textoSecundario);
    return `
<div class="nx-swal-pendencias-inner">
  <div class="nx-swal-pendencias-header">
    <div class="nx-swal-pendencias-icon-circle">${ICON_AVISO_SVG}</div>
    <h2 class="nx-swal-pendencias-title">${t}</h2>
  </div>
  <div class="nx-swal-pendencias-main">
    <p class="nx-swal-pendencias-sub">${linha}</p>
    <div class="nx-swal-pendencias-box">
      <span class="nx-swal-pendencias-box-icon">${ICON_DOC_SVG}</span>
      <div class="nx-swal-pendencias-box-text">
        <p class="nx-swal-pendencias-box-strong">${frase}</p>
      </div>
    </div>
    <p class="nx-swal-pendencias-pergunta">${sec}</p>
  </div>
</div>`;
}

/**
 * @param {object} opts
 * @param {string} opts.titulo
 * @param {string} opts.linhaProcesso — ex.: «Tipo — Cliente»
 * @param {string} opts.frasePendentes — ex.: «3 documentos obrigatórios pendentes»
 * @param {string} opts.textoSecundario
 */
export function fireSwalPendenciasLayout(opts) {
    const titulo = opts.titulo?.trim() || TITULO_SWAL_PADRAO;
    const linhaProcesso = opts.linhaProcesso?.trim() || '—';
    const frasePendentes = opts.frasePendentes?.trim() || FRASE_PENDENTES_PADRAO;
    const textoSecundario = opts.textoSecundario?.trim() || TEXTO_SEC_PADRAO;

    return Swal.fire({
        title: false,
        icon: false,
        html: buildPendenciasDialogHtml({ titulo, linhaProcesso, frasePendentes, textoSecundario }),
        showCloseButton: false,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Sim, continuar',
        cancelButtonText: 'Não, cancelar',
        reverseButtons: true,
        focusCancel: true,
        buttonsStyling: false,
        customClass: {
            popup: 'nx-swal-pendencias-popup',
            actions: 'nx-swal-pendencias-actions',
            confirmButton: 'nx-swal-pendencias-btn-sim',
            cancelButton: 'nx-swal-pendencias-btn-nao',
        },
        background: '#ffffff',
        color: '#0f172a',
        width: 420,
        padding: 0,
    });
}

function formUsaCiencia(form) {
    return form instanceof HTMLFormElement && form.dataset.nxStatusCienciaForm === '1';
}

function requerCienciaDocumental(form) {
    return form.dataset.nxRequerCiencia === '1';
}

function tituloSwalDoForm(form) {
    const t = form?.dataset?.nxSwalTitulo;
    if (t != null && String(t).trim() !== '') {
        return t;
    }
    return TITULO_SWAL_PADRAO;
}

function linhaProcessoDoForm(form) {
    const s = form?.dataset?.nxCienciaLinha;
    if (s != null && String(s).trim() !== '') {
        return s;
    }
    return '—';
}

function frasePendentesDoForm(form) {
    const s = form?.dataset?.nxCienciaFrasePendentes;
    if (s != null && String(s).trim() !== '') {
        return s;
    }
    return FRASE_PENDENTES_PADRAO;
}

function textoSecundarioDoForm(form) {
    const s = form?.dataset?.nxCienciaTextoSecundario;
    if (s != null && String(s).trim() !== '') {
        return s;
    }
    return TEXTO_SEC_PADRAO;
}

function statusAtualDoForm(form) {
    return form.dataset.nxStatusAtual ?? '';
}

const STATUS_EM_MONTAGEM = 'em_montagem';

/** Só pede ciência ao sair de «Em montagem» para outra etapa (alinhado ao backend). */
function isTransicaoMontagemParaOutroStatus(form) {
    const atual = statusAtualDoForm(form);
    const sel = form.querySelector('select[name="status"]');
    if (!sel || atual === '') {
        return false;
    }
    return atual === STATUS_EM_MONTAGEM && sel.value !== STATUS_EM_MONTAGEM;
}

/**
 * Kanban: objeto com titulo, linhaProcesso, frasePendentes, textoSecundario.
 * Legado: (bodyString, titleString) ignorado — usar objeto.
 * @param {object|string} optsOrLegacyBody
 * @param {string} [legacyTitle]
 */
export function swalConfirmarCienciaDocumental(optsOrLegacyBody, legacyTitle) {
    if (typeof optsOrLegacyBody === 'object' && optsOrLegacyBody !== null) {
        return fireSwalPendenciasLayout(optsOrLegacyBody);
    }
    return fireSwalPendenciasLayout({
        titulo: legacyTitle || TITULO_SWAL_PADRAO,
        linhaProcesso: '—',
        frasePendentes: String(optsOrLegacyBody || FRASE_PENDENTES_PADRAO),
        textoSecundario: TEXTO_SEC_PADRAO,
    });
}

window.Swal = Swal;
window.nxSwalConfirmarCienciaDocumental = (a, b) => swalConfirmarCienciaDocumental(a, b);
window.nxSwalExcluirProcesso = (opts) => fireSwalExcluirProcessoLayout(opts);
window.nxSwalTrocarAnexo = (opts) => fireSwalTrocarAnexoLayout(opts);

const ATTR_DESTROY_PROCESSO = 'data-nx-destroy-processo';

function formExcluirProcesso(form) {
    return form instanceof HTMLFormElement && form.getAttribute(ATTR_DESTROY_PROCESSO) === '1';
}

function textosExcluirDoForm(form) {
    const ds = form?.dataset ?? {};
    return {
        titulo: ds.nxExcluirTitulo?.trim() || TITULO_EXCLUIR_PADRAO,
        linhaProcesso: ds.nxProcessoLinha?.trim() || '—',
        fraseAviso: ds.nxExcluirAviso?.trim() || FRASE_AVISO_EXCLUIR_PADRAO,
        textoPergunta: ds.nxExcluirPergunta?.trim() || PERGUNTA_EXCLUIR_PADRAO,
        confirmButtonText: ds.nxExcluirBtnSim?.trim() || BTN_SIM_EXCLUIR_PADRAO,
        cancelButtonText: ds.nxExcluirBtnNao?.trim() || BTN_NAO_EXCLUIR_PADRAO,
    };
}

function registerDestroyProcessoSubmitCapture() {
    document.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || !formExcluirProcesso(form)) {
                return;
            }
            if (form.dataset.nxDestroyProcessoConfirmed === '1') {
                delete form.dataset.nxDestroyProcessoConfirmed;
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            const msgs = textosExcluirDoForm(form);
            void fireSwalExcluirProcessoLayout(msgs).then((r) => {
                if (r.isConfirmed) {
                    form.dataset.nxDestroyProcessoConfirmed = '1';
                    form.requestSubmit();
                }
            });
        },
        true,
    );
}

registerDestroyProcessoSubmitCapture();

const ATTR_TROCAR_ANEXO_FORM = 'data-nx-trocar-anexo-form';

function formTrocarAnexoDigital(form) {
    return form instanceof HTMLFormElement && form.getAttribute(ATTR_TROCAR_ANEXO_FORM) === '1';
}

function textosTrocarAnexoDoForm(form) {
    const ds = form?.dataset ?? {};
    const fraseExplicit = ds.nxTrocarAnexoFrase?.trim();
    const perguntaExplicit = ds.nxTrocarAnexoPergunta?.trim();
    const textoLegacy = ds.nxTrocarAnexoTexto?.trim();

    return {
        titulo: ds.nxTrocarAnexoTitulo?.trim() || TITULO_TROCAR_ANEXO_PADRAO,
        linhaProcesso: ds.nxTrocarAnexoLinha?.trim() || '—',
        fraseAviso: fraseExplicit || textoLegacy || FRASE_AVISO_TROCAR_ANEXO_PADRAO,
        textoPergunta: perguntaExplicit || PERGUNTA_TROCAR_ANEXO_PADRAO,
        confirmButtonText: ds.nxTrocarAnexoConfirmar?.trim() || BTN_SIM_TROCAR_ANEXO_PADRAO,
        cancelButtonText: ds.nxTrocarAnexoCancelar?.trim() || BTN_NAO_TROCAR_ANEXO_PADRAO,
    };
}

function registerTrocarAnexoSubmitCapture() {
    document.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || !formTrocarAnexoDigital(form)) {
                return;
            }
            if (form.dataset.nxTrocarAnexoConfirmed === '1') {
                delete form.dataset.nxTrocarAnexoConfirmed;

                return;
            }
            e.preventDefault();
            e.stopPropagation();
            const t = textosTrocarAnexoDoForm(form);
            void fireSwalTrocarAnexoLayout({
                titulo: t.titulo,
                linhaProcesso: t.linhaProcesso,
                fraseAviso: t.fraseAviso,
                textoPergunta: t.textoPergunta,
                confirmButtonText: t.confirmButtonText,
                cancelButtonText: t.cancelButtonText,
            }).then((r) => {
                if (r.isConfirmed) {
                    form.dataset.nxTrocarAnexoConfirmed = '1';
                    form.requestSubmit();
                }
            });
        },
        true,
    );
}

registerTrocarAnexoSubmitCapture();

function precisaBloquearSubmitParaCiencia(form) {
    if (!formUsaCiencia(form) || !requerCienciaDocumental(form)) {
        return false;
    }
    if (!isTransicaoMontagemParaOutroStatus(form)) {
        return false;
    }
    const hid = form.querySelector(`input[name="${INPUT_CIENCIA}"]`);
    if (!hid || hid.value === '1') {
        return false;
    }
    const sel = form.querySelector('select[name="status"]');
    const atual = statusAtualDoForm(form);
    if (sel && atual !== '' && sel.value === atual) {
        return false;
    }
    return true;
}

/**
 * @returns {Promise<boolean>}
 */
async function executarDialogoCiencia(form) {
    const hid = form.querySelector(`input[name="${INPUT_CIENCIA}"]`);
    const sel = form.querySelector('select[name="status"]');
    const atual = statusAtualDoForm(form);
    const r = await fireSwalPendenciasLayout({
        titulo: tituloSwalDoForm(form),
        linhaProcesso: linhaProcessoDoForm(form),
        frasePendentes: frasePendentesDoForm(form),
        textoSecundario: textoSecundarioDoForm(form),
    });
    if (!r.isConfirmed) {
        if (sel && atual !== '') {
            sel.value = atual;
        }
        return false;
    }
    hid.value = '1';
    return true;
}

function registerSubmitCapture() {
    document.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (!precisaBloquearSubmitParaCiencia(form)) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            void executarDialogoCiencia(form).then((ok) => {
                if (ok) {
                    form.requestSubmit();
                }
            });
        },
        true,
    );
}

function registerSelectChangeCapture() {
    document.addEventListener(
        'change',
        (e) => {
            const sel = e.target;
            if (!(sel instanceof HTMLSelectElement) || sel.name !== 'status') {
                return;
            }
            const form = sel.form;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (form.getAttribute(ATTR_SUBMIT_ON_CHANGE) !== '1') {
                return;
            }

            const hid = form.querySelector(`input[name="${INPUT_CIENCIA}"]`);
            if (hid) {
                hid.value = '0';
            }

            const atual = statusAtualDoForm(form);
            if (atual !== '' && sel.value === atual) {
                return;
            }

            if (!precisaBloquearSubmitParaCiencia(form)) {
                e.stopImmediatePropagation();
                form.requestSubmit();
                return;
            }

            e.stopImmediatePropagation();
            void executarDialogoCiencia(form).then((ok) => {
                if (ok) {
                    form.requestSubmit();
                }
            });
        },
        true,
    );
}

registerSubmitCapture();
registerSelectChangeCapture();

function bindSelectResetsCiencia() {
    document.querySelectorAll('form[data-nx-status-ciencia-form="1"]').forEach((form) => {
        if (form.getAttribute(ATTR_SUBMIT_ON_CHANGE) === '1') {
            return;
        }
        if (form.dataset.nxCienciaSelectBound === '1') {
            return;
        }
        form.dataset.nxCienciaSelectBound = '1';
        const hid = form.querySelector(`input[name="${INPUT_CIENCIA}"]`);
        if (!hid) {
            return;
        }
        form.querySelector('select[name="status"]')?.addEventListener('change', () => {
            hid.value = '0';
        });
    });
}

export function initProcessoStatusFormConfirm() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSelectResetsCiencia);
    } else {
        bindSelectResetsCiencia();
    }
}

/** @returns {Promise<boolean>} */
export async function nxProcessoStatusConfirm(form) {
    if (!precisaBloquearSubmitParaCiencia(form)) {
        return true;
    }
    return executarDialogoCiencia(form);
}

window.nxProcessoStatusConfirm = nxProcessoStatusConfirm;



