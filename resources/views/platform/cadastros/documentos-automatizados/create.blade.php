<x-platform-layout :title="__('Novo documento automático global')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Novo documento automático global') }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Será criado um registo por empresa (não personalizada) com este conteúdo.') }}</p>
    </x-slot>

    <div class="max-w-4xl space-y-4">
        <form method="POST" action="{{ route('platform.cadastros.documentos-automatizados.store') }}" class="space-y-4">
            @csrf
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }} <span class="text-slate-400">(a-z, números, hífen)</span></label>
                    <input name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Título') }}</label>
                    <input name="titulo" value="{{ old('titulo') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('titulo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Referência') }}</label>
                    <input name="referencia" value="{{ old('referencia') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('referencia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Conteúdo Blade') }}</label>
                    <textarea name="conteudo" rows="18" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-xs leading-relaxed dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('conteudo') }}</textarea>
                    @error('conteudo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar') }}</button>
                <a href="{{ route('platform.cadastros.documentos-automatizados.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>
