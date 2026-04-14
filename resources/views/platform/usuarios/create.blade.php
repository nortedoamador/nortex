<x-platform-layout :title="__('Novo utilizador')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Novo utilizador') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Crie um utilizador e atribua-o diretamente a uma empresa com os papéis adequados.') }}</p>
            </div>
            <a href="{{ route('platform.usuarios.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('← Utilizadores') }}</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="POST" action="{{ route('platform.usuarios.store') }}" class="space-y-5" x-data="{ convite: {{ old('enviar_convite') ? 'true' : 'false' }}, empresa: '{{ (string) old('empresa_id', $selectedEmpresaId ?: '') }}' }">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Empresa') }}</label>
                    <select name="empresa_id" x-model="empresa" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">{{ __('Selecione uma empresa') }}</option>
                        @foreach ($empresas as $e)
                            <option value="{{ $e->id }}">{{ $e->nome }}</option>
                        @endforeach
                    </select>
                    @error('empresa_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                        <input name="name" value="{{ old('name') }}" required autofocus class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('E-mail') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="checkbox"
                            name="enviar_convite"
                            value="1"
                            class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                            x-model="convite"
                        />
                        <span>
                            <span class="block text-sm font-medium text-slate-800 dark:text-slate-200">{{ __('Convite por e-mail') }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ __('O novo utilizador recebe um link para definir a senha. Exige SMTP configurado.') }}</span>
                        </span>
                    </label>
                </div>

                <div x-show="!convite" x-cloak class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Senha') }}</label>
                        <input type="password" name="password" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" autocomplete="new-password" x-bind:disabled="convite" x-bind:required="!convite" />
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Confirmar senha') }}</label>
                        <input type="password" name="password_confirmation" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" x-bind:disabled="convite" x-bind:required="!convite" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Papéis') }}</label>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Selecione ao menos um papel da empresa escolhida.') }}</p>

                    <div class="mt-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        <div x-show="empresa === ''" x-cloak class="py-4 text-sm text-slate-500">
                            {{ __('Escolha primeiro a empresa para carregar os papéis disponíveis.') }}
                        </div>

                        @foreach ($empresas as $e)
                            @php
                                $empresaRoles = $rolesByEmpresa->get($e->id, collect());
                            @endphp
                            <div x-show="empresa === '{{ $e->id }}'" x-cloak class="space-y-2">
                                @if ($empresaRoles->isEmpty())
                                    <p class="text-sm text-slate-500">{{ __('Esta empresa ainda não tem papéis disponíveis.') }}</p>
                                @else
                                    @foreach ($empresaRoles as $role)
                                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                            <input
                                                type="checkbox"
                                                name="roles[]"
                                                value="{{ $role->id }}"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                                x-bind:disabled="empresa !== '{{ $e->id }}'"
                                                @checked(collect(old('roles', []))->contains($role->id) || collect(old('roles', []))->contains((string) $role->id))
                                            />
                                            <span>{{ $role->name }} <span class="text-xs text-slate-500">({{ $role->slug }})</span></span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @error('roles')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('roles.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar utilizador') }}</button>
                    <a href="{{ route('platform.usuarios.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-platform-layout>
