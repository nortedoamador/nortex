<x-platform-layout :title="__('Novo tipo de anexo')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Novo tipo de anexo') }}</h2>
    </x-slot>

    <div class="max-w-xl space-y-4">
        <form method="POST" action="{{ route('platform.cadastros.anexo-tipos.store') }}" class="space-y-4">
            @csrf
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                    <input name="nome" value="{{ old('nome') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }}</label>
                    <input name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Ordem') }}</label>
                        <input type="number" min="0" max="32767" name="ordem" value="{{ old('ordem', 0) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('ordem')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="hidden" name="ativo" value="0" />
                            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', true)) class="rounded border-slate-300 text-violet-600" />
                            {{ __('Ativo') }}
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Tamanho máximo (MB)') }}</label>
                        <input type="number" min="1" max="2048" name="max_size_mb" value="{{ old('max_size_mb', 20) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('max_size_mb')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="hidden" name="is_multiple" value="0" />
                            <input type="checkbox" name="is_multiple" value="1" @checked(old('is_multiple', true)) class="rounded border-slate-300 text-violet-600" />
                            {{ __('Permite múltiplos') }}
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('MIME types permitidos') }}</label>
                    <textarea name="allowed_mime_types" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="application/pdf, image/jpeg">{{ old('allowed_mime_types') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Separe por vírgula ou por linha. Em branco = qualquer.') }}</p>
                    @error('allowed_mime_types')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Extensões permitidas') }}</label>
                    <textarea name="allowed_extensions" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="pdf, jpg, png">{{ old('allowed_extensions') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Separe por vírgula ou por linha. Em branco = qualquer.') }}</p>
                    @error('allowed_extensions')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                @include('platform.cadastros.anexo-tipos.partials.contexto-modulos-checkboxes', [
                    'selected' => is_array(old('contexto_modulos')) ? old('contexto_modulos') : [],
                ])
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar') }}</button>
                <a href="{{ route('platform.cadastros.anexo-tipos.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>

