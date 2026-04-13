<x-platform-layout :title="__('Editar usuário')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar usuário') }} — {{ $user->email }}</h2>
    </x-slot>

    <div class="max-w-xl space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('platform.usuarios.update', $user) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
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
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="is_platform_admin" value="0" />
                        <input type="checkbox" name="is_platform_admin" value="1" @checked(old('is_platform_admin', $user->is_platform_admin)) class="rounded border-slate-300 text-violet-600" />
                        {{ __('Admin master da plataforma') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="is_disabled" value="0" />
                        <input type="checkbox" name="is_disabled" value="1" @checked(old('is_disabled', $user->is_disabled)) class="rounded border-slate-300 text-violet-600" />
                        {{ __('Conta desativada (bloquear login)') }}
                    </label>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                <a href="{{ route('platform.usuarios.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
            </div>
        </form>

        <form method="POST" action="{{ route('platform.usuarios.password-reset', $user) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Senha') }}</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Envia um e-mail com link para redefinir a senha (requer SMTP configurado).') }}</p>
            <div class="mt-3">
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Enviar reset de senha') }}</button>
            </div>
        </form>

        @if (auth()->user()?->is_platform_admin)
            <form method="POST" action="{{ route('platform.impersonate.start', $user) }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/30">
                @csrf
                <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('Impersonate') }}</h3>
                <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">{{ __('Entrar como este usuário para suporte (com auditoria).') }}</p>
                <div class="mt-3">
                    <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Iniciar impersonate') }}</button>
                </div>
            </form>
        @endif
    </div>
</x-platform-layout>

