import BrMap from './vendor/brmap';

const UF_LABELS = {
    AC: 'Acre',
    AL: 'Alagoas',
    AP: 'Amapa',
    AM: 'Amazonas',
    BA: 'Bahia',
    CE: 'Ceara',
    DF: 'Distrito Federal',
    ES: 'Espirito Santo',
    GO: 'Goias',
    MA: 'Maranhao',
    MT: 'Mato Grosso',
    MS: 'Mato Grosso do Sul',
    MG: 'Minas Gerais',
    PA: 'Para',
    PB: 'Paraiba',
    PR: 'Parana',
    PE: 'Pernambuco',
    PI: 'Piaui',
    RJ: 'Rio de Janeiro',
    RN: 'Rio Grande do Norte',
    RS: 'Rio Grande do Sul',
    RO: 'Rondonia',
    RR: 'Roraima',
    SC: 'Santa Catarina',
    SP: 'Sao Paulo',
    SE: 'Sergipe',
    TO: 'Tocantins',
};

const PALETTE = ['#f2f1ff', '#e3defe', '#c7bbfd', '#a38cf9', '#5b3df5'];

function normalizeUf(value) {
    return String(value || '').trim().toUpperCase();
}

function colorForCount(count) {
    if (count <= 0) return PALETTE[0];
    if (count === 1) return PALETTE[1];
    if (count <= 3) return PALETTE[2];
    if (count <= 6) return PALETTE[3];

    return PALETTE[4];
}

function labelColorForCount(count, isSelected) {
    if (isSelected || count >= 4) {
        return '#ffffff';
    }

    return count === 0 ? '#8d88b6' : '#5b4db1';
}

function updateLegend(legend, title, meta, uf, stats, selectedUf) {
    if (!legend || !title || !meta) {
        return;
    }

    const normalized = normalizeUf(uf);

    if (!normalized) {
        if (selectedUf) {
            const selectedStats = stats[selectedUf] || { empresas: 0, usuarios: 0 };
            title.textContent = `${UF_LABELS[selectedUf] || selectedUf} (${selectedUf})`;
            meta.textContent = `${selectedStats.empresas || 0} empresa${selectedStats.empresas === 1 ? '' : 's'} · ${selectedStats.usuarios || 0} utilizadores`;
            legend.classList.remove('is-hidden');
            return;
        }

        legend.classList.add('is-hidden');
        title.textContent = '';
        meta.textContent = '';
        return;
    }

    const stateStats = stats[normalized] || { empresas: 0, usuarios: 0 };
    title.textContent = `${UF_LABELS[normalized] || normalized} (${normalized})`;
    meta.textContent = `${stateStats.empresas || 0} empresa${stateStats.empresas === 1 ? '' : 's'} · ${stateStats.usuarios || 0} utilizadores`;
    legend.classList.remove('is-hidden');
}

function updateSubtitle(subtitle, uf, count, selectedUf) {
    if (!subtitle) {
        return;
    }

    const normalized = normalizeUf(uf);

    if (!normalized) {
        if (selectedUf) {
            subtitle.textContent = `Dados filtrados por ${UF_LABELS[selectedUf] || selectedUf}.`;
            return;
        }

        subtitle.textContent = 'Clique num estado para filtrar';
        return;
    }

    subtitle.textContent = `${UF_LABELS[normalized] || normalized}: ${count} empresa${count === 1 ? '' : 's'}`;
}

function styleMap(mapElement, counts, selectedUf, hoveredUf = '') {
    mapElement.querySelectorAll('.state').forEach((stateElement) => {
        const uf = normalizeUf(stateElement.id.replace('state_', ''));
        const shape = stateElement.querySelector('.shape, .icon_state');
        const label = stateElement.querySelector('.label_icon_state');
        const count = Number(counts[uf] || 0);
        const isSelected = selectedUf === uf;
        const isHovered = hoveredUf === uf;

        if (!shape || !label) {
            return;
        }

        shape.style.fill = colorForCount(count);
        shape.style.stroke = isSelected || isHovered ? '#7c3aed' : 'rgba(255, 255, 255, 0.94)';
        shape.style.strokeWidth = isSelected || isHovered ? '2.4' : '1.8';
        shape.style.filter = isSelected || isHovered ? 'drop-shadow(0 12px 20px rgba(124, 58, 237, 0.28))' : 'none';
        label.style.fill = isSelected || isHovered ? '#7c3aed' : labelColorForCount(count, isSelected);
        stateElement.dataset.count = String(count);
        stateElement.removeAttribute('title');
    });
}

function initPlatformBrazilMap(element) {
    const stats = JSON.parse(element.dataset.mapStats || '{}');
    const counts = JSON.parse(element.dataset.mapCounts || '{}');
    const selectedUf = normalizeUf(element.dataset.selectedUf);
    const subtitle = document.getElementById(element.dataset.subtitleId || '');
    const filterForm = document.getElementById(element.dataset.formId || '');
    const ufInput = document.getElementById(element.dataset.inputId || '');
    const legend = document.getElementById('platformMapLegend');
    const legendTitle = document.getElementById('platformMapLegendTitle');
    const legendMeta = document.getElementById('platformMapLegendMeta');
    let hoveredUf = '';

    BrMap.Draw({
        wrapper: `#${element.id}`,
        callbacks: {
            click: (_, rawUf) => {
                if (!filterForm || !ufInput) {
                    return;
                }

                const uf = normalizeUf(rawUf);
                hoveredUf = uf;
                styleMap(element, counts, selectedUf, hoveredUf);
                updateLegend(legend, legendTitle, legendMeta, uf, stats, selectedUf);
                ufInput.value = uf === selectedUf ? '' : uf;
                filterForm.submit();
            },
            mouseover: (_, rawUf) => {
                const uf = normalizeUf(rawUf);
                updateSubtitle(subtitle, uf, Number(counts[uf] || 0), selectedUf);
                hoveredUf = uf;
                styleMap(element, counts, selectedUf, hoveredUf);
                updateLegend(legend, legendTitle, legendMeta, uf, stats, selectedUf);
            },
        },
    });

    const svg = element.querySelector('#brmap');

    if (!svg) {
        return;
    }

    styleMap(element, counts, selectedUf, hoveredUf);

    element.querySelectorAll('.state').forEach((stateElement) => {
        stateElement.addEventListener('mouseleave', () => {
            hoveredUf = '';
            updateSubtitle(subtitle, '', 0, selectedUf);
            styleMap(element, counts, selectedUf, hoveredUf);
            updateLegend(legend, legendTitle, legendMeta, '', stats, selectedUf);
        });
    });

    updateSubtitle(subtitle, '', 0, selectedUf);
    updateLegend(legend, legendTitle, legendMeta, '', stats, selectedUf);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-platform-brazil-map]').forEach((element) => {
        initPlatformBrazilMap(element);
    });
});
