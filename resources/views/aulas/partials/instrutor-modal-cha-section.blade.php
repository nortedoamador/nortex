<div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Habilitação CHA (carteira do instrutor)') }}</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Opcional. Pode completar ou alterar depois na aba Carteira.') }}</p>
    <div class="mt-3 grid gap-3 md:grid-cols-2">
        <div>
            <x-input-label for="novo_ins_cha_n" :value="__('Nº CHA')" />
            <x-text-input id="novo_ins_cha_n" data-instrutor-cha="numero" class="mt-1 block w-full" />
        </div>
        <div>
            <x-input-label for="novo_ins_cha_cat" :value="__('Categoria CHA')" />
            <x-text-input id="novo_ins_cha_cat" data-instrutor-cha="categoria" class="mt-1 block w-full" />
        </div>
        <div>
            <x-input-label for="novo_ins_cha_de" :value="__('Data emissão CHA')" />
            <input id="novo_ins_cha_de" type="date" data-instrutor-cha="data_emissao" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
        </div>
        <div>
            <x-input-label for="novo_ins_cha_dv" :value="__('Validade CHA')" />
            <input id="novo_ins_cha_dv" type="date" data-instrutor-cha="data_validade" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
        </div>
        <div class="md:col-span-2">
            <x-input-label for="novo_ins_cha_j" :value="__('Jurisdição CHA')" />
            <x-text-input id="novo_ins_cha_j" data-instrutor-cha="jurisdicao" class="mt-1 block w-full" />
        </div>
    </div>
</div>
