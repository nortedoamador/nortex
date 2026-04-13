@can('update', $embarcacao)
    <div class="space-y-5">
        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Enviar novas fotos') }}</p>

        <form
            method="POST"
            action="{{ route('embarcacoes.fotos-cadastro.store', $embarcacao) }}"
            enctype="multipart/form-data"
            class="rounded-xl border border-slate-200/90 bg-slate-50/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/30"
        >
            @csrf
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Foto do través (vista lateral)') }}</p>
            <input
                type="file"
                name="foto_traves"
                accept=".jpg,.jpeg,.png,.webp,image/*"
                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/40 dark:border-slate-600 dark:bg-slate-900 dark:file:bg-slate-800 dark:file:text-slate-200"
            />
            <x-input-error :messages="$errors->get('foto_traves')" class="mt-2" />
            <button
                type="submit"
                class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 dark:focus:ring-violet-400/30"
            >
                {{ __('Enviar') }}
            </button>
        </form>

        <form
            method="POST"
            action="{{ route('embarcacoes.fotos-cadastro.store', $embarcacao) }}"
            enctype="multipart/form-data"
            class="rounded-xl border border-slate-200/90 bg-slate-50/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/30"
        >
            @csrf
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Foto da popa') }}</p>
            <input
                type="file"
                name="foto_popa"
                accept=".jpg,.jpeg,.png,.webp,image/*"
                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/40 dark:border-slate-600 dark:bg-slate-900 dark:file:bg-slate-800 dark:file:text-slate-200"
            />
            <x-input-error :messages="$errors->get('foto_popa')" class="mt-2" />
            <button
                type="submit"
                class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 dark:focus:ring-violet-400/30"
            >
                {{ __('Enviar') }}
            </button>
        </form>

        <form
            method="POST"
            action="{{ route('embarcacoes.fotos-cadastro.store', $embarcacao) }}"
            enctype="multipart/form-data"
            class="rounded-xl border border-slate-200/90 bg-slate-50/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/30"
        >
            @csrf
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Outras fotos da embarcação') }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{{ __('Pode selecionar várias imagens.') }}</p>
            <label class="mt-3 block text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="ficha-fotos-outras-rotulo">{{ __('Descrição das fotos') }}</label>
            <input
                id="ficha-fotos-outras-rotulo"
                type="text"
                name="fotos_outras_rotulo"
                value="{{ old('fotos_outras_rotulo') }}"
                maxlength="255"
                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                placeholder="{{ __('Obrigatório ao enviar outras fotos') }}"
            />
            <input
                type="file"
                name="fotos_outras[]"
                multiple
                accept=".jpg,.jpeg,.png,.webp,image/*"
                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/40 dark:border-slate-600 dark:bg-slate-900 dark:file:bg-slate-800 dark:file:text-slate-200"
            />
            <x-input-error :messages="$errors->get('fotos_outras_rotulo')" class="mt-2" />
            <x-input-error :messages="$errors->get('fotos_outras')" class="mt-2" />
            <x-input-error :messages="$errors->get('fotos_outras.*')" class="mt-2" />
            <button
                type="submit"
                class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 dark:focus:ring-violet-400/30"
            >
                {{ __('Enviar') }}
            </button>
        </form>
    </div>
@endcan
