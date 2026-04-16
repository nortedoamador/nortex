<x-platform-layout :title="__('Novo tipo de documento (global)')">
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Novo tipo de documento (global)') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ __('Empresa de referência: «:e» (ID :id).', ['e' => $checklistEmpresa->nome, 'id' => $checklistEmpresa->id]) }}
            </p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('platform.cadastros.checklist-documentos.store') }}" class="space-y-4">
            @csrf
            <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Código') }}</label>
                    <input name="codigo" value="{{ old('codigo') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('codigo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                    <input name="nome" value="{{ old('nome') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="auto_gerado" value="1" @checked(old('auto_gerado')) class="rounded border-slate-300 text-violet-600" />
                        {{ __('Documento gerado automaticamente') }}
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug do modelo (opcional)') }}</label>
                    <input name="modelo_slug" value="{{ old('modelo_slug') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('modelo_slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                <a href="{{ route('platform.cadastros.checklist-documentos.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>
