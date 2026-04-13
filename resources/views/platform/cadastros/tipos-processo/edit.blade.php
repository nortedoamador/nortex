<x-platform-layout :title="__('Editar tipo de processo')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar tipo de processo') }} — {{ $tipo->nome }}</h2>
    </x-slot>

    <div class="max-w-xl space-y-4">
        <form method="POST" action="{{ route('platform.cadastros.tipos-processo.update', $tipo) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                    <input name="nome" value="{{ old('nome', $tipo->nome) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }}</label>
                    <input name="slug" value="{{ old('slug', $tipo->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Categoria') }}</label>
                    <select name="categoria" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">{{ __('(sem categoria)') }}</option>
                        @foreach ($categorias as $c)
                            <option value="{{ $c->value }}" @selected(old('categoria', $tipo->categoria) === $c->value)>{{ $c->label() }}</option>
                        @endforeach
                    </select>
                    @error('categoria')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Ordem') }}</label>
                        <input type="number" min="0" max="32767" name="ordem" value="{{ old('ordem', $tipo->ordem) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('ordem')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="hidden" name="ativo" value="0" />
                            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $tipo->ativo)) class="rounded border-slate-300 text-violet-600" />
                            {{ __('Ativo') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                <a href="{{ route('platform.cadastros.tipos-processo.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>

