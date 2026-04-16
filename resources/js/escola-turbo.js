import '@hotwired/turbo';
import { initCnpjMasks } from './cnpj-mask';
import { initDateBrMasks } from './date-br-mask';
import { initClienteFichaMasks } from './cliente-ficha-masks';
import { initClienteFichaDoc } from './cliente-ficha-doc';
import { initClienteFichaGeo } from './cliente-ficha-geo';

/**
 * @param {ParentNode} root
 */
function bindClienteFichaFormsIn(root) {
    root.querySelectorAll('form[data-cliente-ficha]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.nxClienteFichaReady === '1') {
            return;
        }
        form.dataset.nxClienteFichaReady = '1';
        initClienteFichaMasks(form);
        initClienteFichaDoc(form);
        initClienteFichaGeo(form);
    });
}

document.addEventListener('turbo:frame-load', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement) || target.id !== 'nx-escola-hub') {
        return;
    }
    if (window.Alpine) {
        window.Alpine.initTree(target);
    }
    initDateBrMasks(target);
    initCnpjMasks(target);
    bindClienteFichaFormsIn(target);
});

document.addEventListener('turbo:frame-render', async (event) => {
    const frame = event.target;
    if (!(frame instanceof HTMLElement) || frame.id !== 'nx-escola-hub') {
        return;
    }
    const fr = event.detail?.fetchResponse;
    if (!fr) return;
    try {
        const htmlString = await fr.responseHTML;
        if (!htmlString || typeof htmlString !== 'string') return;
        const doc = new DOMParser().parseFromString(htmlString, 'text/html');
        const titleEl = doc.querySelector('title');
        if (titleEl?.textContent) {
            document.title = titleEl.textContent.trim();
        }
    } catch {
        /* ignore */
    }
});
