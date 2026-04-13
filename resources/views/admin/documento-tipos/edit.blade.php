<x-tenant-admin-layout title="{{ __('Editar tipo de documento') }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar tipo de documento') }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl">
            <form method="POST" action="{{ tenant_admin_route('documento-tipos.update', $documentoTipo) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Código') }}</label>
                        <input name="codigo" value="{{ old('codigo', $documentoTipo->codigo) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('codigo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                        <input name="nome" value="{{ old('nome', $documentoTipo->nome) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="auto_gerado" value="1" @checked(old('auto_gerado', $documentoTipo->auto_gerado)) class="rounded border-slate-300 text-indigo-600" />
                            {{ __('Documento gerado automaticamente') }}
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug do modelo (opcional)') }}</label>
                        <input name="modelo_slug" value="{{ old('modelo_slug', $documentoTipo->modelo_slug) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('modelo_slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                    <a href="{{ tenant_admin_route('documento-tipos.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-tenant-admin-layout>
