/**
 * Alpine factory para a listagem AJAX da visão geral de aulas (Escola Náutica).
 * O estado inicial HTML vem do Blade (initialTagsHtml, etc.).
 */
export function registerNxAulasIndex() {
    window.nxAulasIndex = ({
        baseUrl,
        initial,
        initialTagsHtml = '',
        initialRowsHtml = '',
        initialPaginationHtml = '',
    }) => ({
        filtrosOpen: false,
        state: {
            q: initial.q ?? '',
            numero_oficio: initial.numero_oficio ?? '',
            data: initial.data ?? '',
            instrutor: initial.instrutor ?? '',
            aluno: initial.aluno ?? '',
            tipo_aula: initial.tipo_aula ?? '',
        },
        html: {
            tags: initialTagsHtml,
            rows: initialRowsHtml,
            pagination: initialPaginationHtml,
        },
        aborter: null,
        filtrosAtivosCount() {
            let n = 0;
            if ((this.state.q || '').trim() !== '') n++;
            if ((this.state.data || '').trim() !== '') n++;
            if ((this.state.numero_oficio || '').trim() !== '') n++;
            if ((this.state.tipo_aula || '').trim() !== '') n++;
            if ((this.state.instrutor || '').trim() !== '') n++;
            if ((this.state.aluno || '').trim() !== '') n++;
            return n;
        },
        onTagsClick(e) {
            const btn = e.target?.closest?.('[data-nx-aulas-rm]');
            if (!btn) return;
            const key = btn.getAttribute('data-nx-aulas-rm');
            if (key === 'q') this.state.q = '';
            else if (key === 'data') this.state.data = '';
            else if (key === 'numero_oficio') this.state.numero_oficio = '';
            else if (key === 'tipo_aula') this.state.tipo_aula = '';
            else if (key === 'instrutor') this.state.instrutor = '';
            else if (key === 'aluno') this.state.aluno = '';
            this.apply();
        },
        init() {
            this.$nextTick(() => {
                const el = this.$refs.pagination;
                if (!el) return;
                el.addEventListener('click', (e) => {
                    const a = e.target?.closest?.('a');
                    if (!a || !a.href) return;
                    e.preventDefault();
                    this.gotoPage(a.href);
                });
            });
        },
        buildParams() {
            const p = new URLSearchParams();
            if ((this.state.q || '').trim() !== '') p.set('q', this.state.q.trim());
            if (this.state.data) p.set('data', this.state.data);
            if (this.state.numero_oficio) p.set('numero_oficio', this.state.numero_oficio);
            if (this.state.instrutor) p.set('instrutor', this.state.instrutor);
            if (this.state.aluno) p.set('aluno', this.state.aluno);
            if (this.state.tipo_aula) p.set('tipo_aula', this.state.tipo_aula);
            return p;
        },
        async apply(url = null) {
            const params = this.buildParams();
            const target = url ? new URL(url) : new URL(baseUrl);
            if (!url) target.searchParams.delete('page');
            params.forEach((v, k) => target.searchParams.set(k, v));
            history.replaceState({}, '', target.toString());
            try {
                if (this.aborter) this.aborter.abort();
                this.aborter = new AbortController();
                const res = await fetch(target.toString(), {
                    headers: { Accept: 'application/json' },
                    signal: this.aborter.signal,
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.tags_html != null) {
                    this.html.tags = data.tags_html;
                }
                this.html.rows = data.rows_html ?? this.html.rows;
                this.html.pagination = data.pagination_html ?? '';
            } catch {
                /* abort / network */
            }
        },
        gotoPage(href) {
            this.apply(href);
        },
    });
}
