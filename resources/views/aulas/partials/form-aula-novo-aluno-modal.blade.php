<x-modal name="novo-aluno-aula" maxWidth="2xl" focusable>
    <div class="p-6" x-data="nxNovoAlunoModal()">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Cadastrar Novo Aluno') }}</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Salvar sem sair da aula. Ao concluir, o aluno será vinculado automaticamente.') }}</p>
            </div>
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="$dispatch('close-modal', 'novo-aluno-aula')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="novo_aluno_nome" :value="__('Nome completo')" />
                <x-text-input id="novo_aluno_nome" x-model="form.nome" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label for="novo_aluno_cpf" :value="__('CPF')" />
                <x-text-input id="novo_aluno_cpf" x-model="form.cpf" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label for="novo_aluno_rg" :value="__('RG')" />
                <x-text-input id="novo_aluno_rg" x-model="form.documento_identidade_numero" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label for="novo_aluno_orgao" :value="__('Órgão emissor')" />
                <x-text-input id="novo_aluno_orgao" x-model="form.orgao_emissor" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label for="novo_aluno_data_emissao" :value="__('Data emissão RG')" />
                <input id="novo_aluno_data_emissao" type="date" x-model="form.data_emissao_rg" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
            </div>
            <div>
                <x-input-label for="novo_aluno_nasc" :value="__('Data nascimento')" />
                <input id="novo_aluno_nasc" type="date" x-model="form.data_nascimento" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
            </div>
            <div>
                <x-input-label for="novo_aluno_tel" :value="__('Telefone')" />
                <x-text-input id="novo_aluno_tel" x-model="form.telefone" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label for="novo_aluno_cat" :value="__('Categoria')" />
                <x-text-input id="novo_aluno_cat" x-model="form.categoria_cnh" class="mt-1 block w-full" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="novo_aluno_end" :value="__('Endereço')" />
                <x-text-input id="novo_aluno_end" x-model="form.endereco" class="mt-1 block w-full" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="novo_aluno_cidade" :value="__('Cidade')" />
                <x-text-input id="novo_aluno_cidade" x-model="form.cidade" class="mt-1 block w-full" />
            </div>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
            <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" @click="$dispatch('close-modal', 'novo-aluno-aula')">{{ __('Cancelar') }}</button>
            <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700" @click="submit()">
                <span x-show="!loading">{{ __('Salvar aluno') }}</span>
                <span x-show="loading">{{ __('Salvando…') }}</span>
            </button>
        </div>
    </div>
</x-modal>
