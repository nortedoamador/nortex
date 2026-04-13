@php
    /** @var string $idPrefix */
    /** @var \App\Models\Embarcacao|null $embarcacao */
    $embarcacao = $embarcacao ?? null;
    $pid = fn (string $id) => $idPrefix.$id;
@endphp
<div class="md:col-span-2">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Fotos da embarcação') }}</p>
        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
            {{ __('Vista lateral (través), vista de popa e outras fotos que quiser (proa, casario, detalhes). Formatos: JPG, PNG ou WebP (até 10 MB por ficheiro).') }}
        </p>

        @if ($embarcacao)
            <div class="mt-4 rounded-xl border border-slate-200/80 bg-white p-3 dark:border-slate-700 dark:bg-slate-900/60">
                @include('embarcacoes.partials.fotos-embarcacao-galeria', ['embarcacao' => $embarcacao, 'mostrarTitulosVazios' => false])
            </div>
        @endif

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="{{ $pid('foto_traves') }}" :value="__('Foto do través (vista lateral):')" />
                <input
                    id="{{ $pid('foto_traves') }}"
                    name="foto_traves"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/*"
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                />
                <x-input-error :messages="$errors->get('foto_traves')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="{{ $pid('foto_popa') }}" :value="__('Foto da popa:')" />
                <input
                    id="{{ $pid('foto_popa') }}"
                    name="foto_popa"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/*"
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                />
                <x-input-error :messages="$errors->get('foto_popa')" class="mt-2" />
            </div>
        </div>
        <div class="mt-4">
            <x-input-label for="{{ $pid('fotos_outras') }}" :value="__('Outras fotos da embarcação:')" />
            <x-input-label class="mt-3" for="{{ $pid('fotos_outras_rotulo') }}" :value="__('Descrição das fotos (obrigatório para outras)')" />
            <input
                id="{{ $pid('fotos_outras_rotulo') }}"
                name="fotos_outras_rotulo"
                type="text"
                value="{{ old('fotos_outras_rotulo') }}"
                maxlength="255"
                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                placeholder="{{ __('Ex.: Motor, interior…') }}"
            />
            <input
                id="{{ $pid('fotos_outras') }}"
                name="fotos_outras[]"
                type="file"
                multiple
                accept=".jpg,.jpeg,.png,.webp,image/*"
                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
            />
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ __('Pode selecionar várias imagens de uma vez.') }}</p>
            <x-input-error :messages="$errors->get('fotos_outras_rotulo')" class="mt-2" />
            <x-input-error :messages="$errors->get('fotos_outras')" class="mt-2" />
            <x-input-error :messages="$errors->get('fotos_outras.*')" class="mt-2" />
        </div>
    </div>
</div>
