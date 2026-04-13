<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Novo usuário') }}</h2>
            <a href="{{ route('equipe.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('← Equipe') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-lg">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <form method="POST" action="{{ route('equipe.store') }}" class="space-y-5" x-data="{ convite: {{ old('enviar_convite') ? 'true' : 'false' }} }">
                    @csrf
                    <div>
                        <x-input-label for="name" value="{{ __('Nome') }}" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="{{ __('E-mail') }}" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
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
                                <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ __('O novo usuário recebe um link para definir a senha (recomendado). Exige SMTP configurado no servidor.') }}</span>
                            </span>
                        </label>
                    </div>
                    <div x-show="!convite" x-cloak class="space-y-5">
                        <div>
                            <x-input-label for="password" value="{{ __('Senha') }}" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" x-bind:disabled="convite" x-bind:required="!convite" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="{{ __('Confirmar senha') }}" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" x-bind:disabled="convite" x-bind:required="!convite" />
                        </div>
                    </div>
                    <div>
                        <x-input-label value="{{ __('Papéis') }}" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Selecione ao menos um papel.') }}</p>
                        <div class="mt-2 space-y-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->id }}"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                        @checked(collect(old('roles', []))->contains($role->id) || collect(old('roles', []))->contains((string) $role->id))
                                    />
                                    <span>{{ $role->name }} <span class="text-xs text-slate-500">({{ $role->slug }})</span></span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                        <x-input-error :messages="$errors->get('roles.*')" class="mt-2" />
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <x-primary-button>{{ __('Criar usuário') }}</x-primary-button>
                        <a href="{{ route('equipe.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
