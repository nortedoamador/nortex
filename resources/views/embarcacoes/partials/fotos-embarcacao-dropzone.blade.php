@can('update', $embarcacao)
    <div
        id="fotos-upload"
        class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-white"
        x-data="embarcacaoFotoDrop({
            action: @js(route('embarcacoes.fotos-cadastro.store', $embarcacao)),
            csrf: @js(csrf_token()),
            msgNoImages: @js(__('Arraste apenas imagens JPG, PNG ou WebP.')),
            msgSingleOnly: @js(__('Para través ou popa envie apenas uma imagem de cada vez.')),
            msgOutrasRequired: @js(__('Descreva o conteúdo destas fotografias.')),
            msgForbidden: @js(__('Sem permissão para enviar fotos.')),
            labelTraves: @js(__('Foto do través (vista lateral)')),
            labelPopa: @js(__('Foto da popa (vista traseira)')),
            labelOutras: @js(__('Outras fotos')),
        })"
    >
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Arrastar e largar fotos') }}</p>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Largue as imagens aqui ou clique para escolher. Depois indique se são do través, da popa ou outras (com descrição).') }}</p>

        <div
            class="mt-3 flex min-h-[11rem] cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-6 text-center transition"
            :class="drag ? 'border-violet-500 bg-white ring-2 ring-violet-500/20 dark:border-violet-400 dark:bg-white dark:ring-violet-400/25' : 'border-slate-300 bg-white dark:border-slate-500 dark:bg-white'"
            @dragenter.prevent="onDragEnter($event)"
            @dragover.prevent="onDragOver($event)"
            @dragleave="onDragLeave($event)"
            @drop.prevent="onDrop($event)"
            @click="$refs.fotoDropFile.click()"
            role="button"
            tabindex="0"
            @keydown.enter.prevent="$refs.fotoDropFile.click()"
            @keydown.space.prevent="$refs.fotoDropFile.click()"
        >
            <div class="flex w-full max-w-md flex-col items-center gap-2 pointer-events-none" x-show="files.length === 0" x-cloak>
                <svg class="h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.25" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.28 4.5 4.5 0 0 1 4.34 4.476 4.5 4.5 0 0 1-6.335 4.46" />
                </svg>
                <span class="text-base font-semibold text-slate-800 dark:text-slate-900">{{ __('Largar imagens ou clicar para selecionar') }}</span>
                <span class="text-sm text-slate-500 dark:text-slate-600">JPG, PNG, WebP · {{ __('máx.') }} 10 MB</span>
            </div>
            <div class="w-full max-w-md px-1 text-left pointer-events-none" x-show="files.length > 0" x-cloak>
                <p class="mb-2 text-center text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-600">{{ __('Ficheiros selecionados') }}</p>
                <ul class="max-h-36 space-y-1.5 overflow-y-auto text-base">
                    <template x-for="(file, idx) in files" :key="idx">
                        <li class="truncate rounded-md bg-slate-100 px-2.5 py-1.5 text-left font-medium text-slate-800 dark:bg-slate-200 dark:text-slate-900" x-text="file.name"></li>
                    </template>
                </ul>
                <p class="mt-2 text-center text-sm text-slate-500 dark:text-slate-600">{{ __('Clique para alterar a seleção') }}</p>
            </div>
            <input
                x-ref="fotoDropFile"
                type="file"
                class="sr-only"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                multiple
                @change="onFileInputChange($event)"
            />
        </div>

        <p x-show="clientError" x-cloak class="mt-2 text-sm font-medium text-red-600 dark:text-red-400" x-text="clientError"></p>
        <x-input-error :messages="$errors->get('foto_traves')" class="mt-2" />
        <x-input-error :messages="$errors->get('foto_popa')" class="mt-2" />
        <x-input-error :messages="$errors->get('fotos_outras')" class="mt-2" />
        <x-input-error :messages="$errors->get('fotos_outras.*')" class="mt-2" />
        <x-input-error :messages="$errors->get('fotos_outras_rotulo')" class="mt-2" />

        <div class="mt-4 space-y-3" x-show="files.length > 0" x-cloak>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    <span x-text="files.length"></span> {{ __('ficheiro(s) selecionado(s)') }}
                </p>
                <button type="button" class="text-sm font-semibold text-violet-600 hover:underline dark:text-violet-400" @click="clearQueue()">{{ __('Limpar') }}</button>
            </div>
            <fieldset class="space-y-2">
                <legend class="sr-only">{{ __('Tipo de fotografia') }}</legend>
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-base dark:border-slate-700 dark:bg-slate-900">
                    <input type="radio" name="nx_tipo_foto" value="traves" x-model="tipo" class="text-violet-600 focus:ring-violet-500" />
                    <span x-text="labelTraves"></span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-base dark:border-slate-700 dark:bg-slate-900">
                    <input type="radio" name="nx_tipo_foto" value="popa" x-model="tipo" class="text-violet-600 focus:ring-violet-500" />
                    <span x-text="labelPopa"></span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-base dark:border-slate-700 dark:bg-slate-900">
                    <input type="radio" name="nx_tipo_foto" value="outras" x-model="tipo" class="text-violet-600 focus:ring-violet-500" />
                    <span x-text="labelOutras"></span>
                </label>
            </fieldset>
            <div x-show="tipo === 'outras'" x-cloak>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="fotos-drop-rotulo">{{ __('Descrição das fotos') }}</label>
                <input
                    id="fotos-drop-rotulo"
                    type="text"
                    x-model="outrasDescricao"
                    maxlength="255"
                    class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                    placeholder="{{ __('Ex.: Motor, interior, equipamento…') }}"
                />
            </div>
            <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-4 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="uploading"
                @click="submit()"
            >
                <span x-show="!uploading">{{ __('Enviar fotos') }}</span>
                <span x-show="uploading" x-cloak>{{ __('A enviar…') }}</span>
            </button>
        </div>
    </div>
@endcan
