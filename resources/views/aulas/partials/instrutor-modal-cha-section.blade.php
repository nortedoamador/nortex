<div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Habilitação CHA (carteira do instrutor)') }}</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Opcional. Pode completar ou alterar depois na aba Carteira.') }}</p>
    <div class="mt-3 grid gap-3 md:grid-cols-2">
        <div>
            <x-input-label for="novo_ins_cha_n" :value="__('Nº CHA')" />
            <x-text-input id="novo_ins_cha_n" data-instrutor-cha="numero" class="mt-1 block w-full" />
        </div>
        <div>
            @include('partials.cha-select-categoria-habilitacao', [
                'id' => 'novo_ins_cha_cat',
                'name' => 'cha_categoria',
                'selected' => '',
                'dataInstrutorCha' => 'categoria',
            ])
        </div>
        <div>
            <x-input-label for="novo_ins_cha_de" :value="__('Data de emissão')" />
            <input
                type="text"
                id="novo_ins_cha_de"
                data-instrutor-cha="data_emissao"
                inputmode="numeric"
                maxlength="10"
                autocomplete="off"
                placeholder="dd/mm/aaaa"
                data-nx-mask="date-br"
                x-data="{
                    brToIso(br) {
                        const s = String(br || '').trim();
                        const m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                        if (!m) return '';
                        return `${m[3]}-${m[2]}-${m[1]}`;
                    },
                    isoToBr(iso) {
                        const s = String(iso || '').trim();
                        const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                        if (!m) return '';
                        return `${m[3]}/${m[2]}/${m[1]}`;
                    },
                    addYears(br, years) {
                        const iso = this.brToIso(br);
                        if (!iso) return '';
                        const d = new Date(String(iso) + 'T00:00:00');
                        if (Number.isNaN(d.getTime())) return '';
                        const y = d.getFullYear() + years;
                        const mo = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return this.isoToBr(`${y}-${mo}-${day}`);
                    },
                    syncValidade(force = false) {
                        const em = this.$el?.value;
                        const vEl = document.getElementById('novo_ins_cha_dv');
                        if (!vEl) return;
                        if (!em) return;
                        if (!force && vEl.dataset.nxManual === '1') return;
                        const next = this.addYears(em, 10);
                        if (next) vEl.value = next;
                    },
                }"
                x-init="syncValidade(false)"
                @change="syncValidade(true)"
                @input="syncValidade(true)"
                class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
            />
        </div>
        <div>
            <x-input-label for="novo_ins_cha_dv" :value="__('Vencimento')" />
            <input
                type="text"
                id="novo_ins_cha_dv"
                data-instrutor-cha="data_validade"
                inputmode="numeric"
                maxlength="10"
                autocomplete="off"
                placeholder="dd/mm/aaaa"
                data-nx-mask="date-br"
                @change="$el.dataset.nxManual = '1'"
                @input="$el.dataset.nxManual = '1'"
                class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
            />
        </div>
        @include('partials.cha-select-jurisdicao-habilitacao', [
            'id' => 'novo_ins_cha_j',
            'name' => 'cha_jurisdicao',
            'selected' => '',
            'dataInstrutorCha' => 'jurisdicao',
            'wrapperClass' => 'md:col-span-2',
        ])
    </div>
</div>
