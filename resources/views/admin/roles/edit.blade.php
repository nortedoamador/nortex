<x-tenant-admin-layout title="{{ __('Editar papel') }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar papel') }} — {{ $papel->name }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <form method="POST" action="{{ tenant_admin_route('roles.update', $papel) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                        <input name="name" value="{{ old('name', $papel->name) }}" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }}</label>
                        @if ($reservado)
                            <input type="text" readonly value="{{ $papel->slug }}" class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400" />
                        @else
                            <input name="slug" value="{{ old('slug', $papel->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>
                    @php
                        $selected = old('permissions', $papel->permissions->pluck('id')->all());
                    @endphp
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">{{ __('Permissões') }}</p>
                        <div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                            @foreach ($permissoes as $perm)
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" @checked(in_array($perm->id, $selected, true)) class="rounded border-slate-300 text-indigo-600" />
                                    <span>{{ $perm->name }}</span>
                                    <code class="text-xs text-slate-400">{{ $perm->slug }}</code>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                    <a href="{{ tenant_admin_route('roles.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200">{{ __('Voltar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-tenant-admin-layout>
