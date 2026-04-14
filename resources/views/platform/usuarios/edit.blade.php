<x-platform-layout :title="__('Editar usuário')">
    <x-slot name="header">
        <div>
            <nav class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('platform.usuarios.index') }}" class="hover:text-violet-600 dark:hover:text-violet-400">{{ __('Utilizadores') }}</a>
                <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
                <span class="text-slate-700 dark:text-slate-200">{{ $user->email }}</span>
            </nav>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar utilizador') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $user->name }} · {{ $user->email }}</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Identidade e organização') }}</h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Dados de perfil e vínculo com a empresa.') }}</p>
                    <form method="POST" action="{{ route('platform.usuarios.update', $user) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                            <input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('E-mail') }}</label>
                            <input name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Empresa') }}</label>
                            <select name="empresa_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="">{{ __('(sem empresa)') }}</option>
                                @foreach ($empresas as $e)
                                    <option value="{{ $e->id }}" @selected((string) old('empresa_id', $user->empresa_id) === (string) $e->id)>{{ $e->nome }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Controlo de acesso') }}</p>
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="hidden" name="is_platform_admin" value="0" />
                                <input type="checkbox" name="is_platform_admin" value="1" @checked(old('is_platform_admin', $user->is_platform_admin)) class="rounded border-slate-300 text-violet-600" />
                                {{ __('Administrador da plataforma (acesso total ao /platform)') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="hidden" name="is_disabled" value="0" />
                                <input type="checkbox" name="is_disabled" value="1" @checked(old('is_disabled', $user->is_disabled)) class="rounded border-slate-300 text-violet-600" />
                                {{ __('Conta desativada (bloquear login)') }}
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                            <a href="{{ route('platform.usuarios.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
                        </div>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Senha') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Envia um e-mail com link para redefinir a senha (requer SMTP configurado).') }}</p>
                    <form method="POST" action="{{ route('platform.usuarios.password-reset', $user) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Enviar reset de senha') }}</button>
                    </form>
                </section>

                @if (auth()->user()?->is_platform_admin)
                    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/30">
                        <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('Suporte (impersonate)') }}</h3>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">{{ __('Entrar como este utilizador para reproduzir o problema. A ação fica registada na auditoria.') }}</p>
                        <form method="POST" action="{{ route('platform.impersonate.start', $user) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Iniciar impersonate') }}</button>
                        </form>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Papéis na empresa') }}</h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('As permissões detalhadas gerem-se nos papéis da organização.') }}</p>
                    @if ($user->empresa_id)
                        <ul class="mt-3 space-y-2">
                            @forelse ($user->roles as $papel)
                                <li class="rounded-lg border border-slate-100 px-3 py-2 text-sm dark:border-slate-800">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $papel->name }}</span>
                                    <span class="mt-0.5 block font-mono text-xs text-slate-500">{{ $papel->slug }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">{{ __('Nenhum papel atribuído.') }}</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('platform.empresas.admin.roles.index', $user->empresa) }}" class="mt-4 inline-flex text-sm font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">
                            {{ __('Gerir papéis desta empresa →') }}
                        </a>
                    @else
                        <p class="mt-3 text-sm text-slate-500">{{ __('Este utilizador não está associado a uma empresa. Atribua uma empresa para poder usar papéis.') }}</p>
                    @endif
                </section>

                @if ($user->empresa_id)
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Organização') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $user->empresa?->nome }}</p>
                        <a href="{{ route('platform.empresas.show', $user->empresa) }}" class="mt-3 inline-flex text-sm font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">
                            {{ __('Abrir painel da empresa →') }}
                        </a>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-platform-layout>
