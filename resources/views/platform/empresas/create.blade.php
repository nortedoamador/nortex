<x-platform-layout :title="__('Nova empresa')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Nova empresa') }}</h2>
    </x-slot>

    <div class="max-w-xl space-y-4">
        <form method="POST" action="{{ route('platform.empresas.store') }}" class="space-y-4">
            @csrf
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                    <input name="nome" value="{{ old('nome') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }} <span class="text-slate-400">(a-z, números, hífen)</span></label>
                    <input name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('CNPJ') }}</label>
                    <input name="cnpj" value="{{ old('cnpj') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('cnpj')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('UF') }}</label>
                    <select name="uf" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">{{ __('Selecione o estado') }}</option>
                        @foreach ($ufs as $sigla => $nome)
                            <option value="{{ $sigla }}" @selected(old('uf') === $sigla)>{{ $sigla }} - {{ $nome }}</option>
                        @endforeach
                    </select>
                    @error('uf')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="ativo" value="0" />
                        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', true)) class="rounded border-slate-300 text-violet-600" />
                        {{ __('Empresa ativa') }}
                    </label>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar') }}</button>
                <a href="{{ route('platform.empresas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>
