<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar: :name', ['name' => $membro->name]) }}</h2>
            <a href="{{ route('equipe.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('← Equipe') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-lg">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif
                <x-input-error :messages="$errors->get('email')" class="mb-4" />
                <form method="POST" action="{{ route('equipe.update', $membro) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4 border-b border-slate-200 pb-6 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Dados da conta') }}</h3>
                        <div>
                            <x-input-label for="name" value="{{ __('Nome') }}" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $membro->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="{{ __('E-mail') }}" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $membro->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password" value="{{ __('Nova senha (opcional)') }}" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Deixe em branco para manter a senha atual.') }}</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="{{ __('Confirmar nova senha') }}" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="{{ __('Papéis') }}" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('A empresa deve manter ao menos um administrador.') }}</p>
                        <div class="mt-2 space-y-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->id }}"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                        @checked($membro->roles->contains('id', $role->id))
                                    />
                                    <span>{{ $role->name }} <span class="text-xs text-slate-500">({{ $role->slug }})</span></span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                        <x-input-error :messages="$errors->get('roles.*')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-primary-button>{{ __('Salvar') }}</x-primary-button>
                        <a href="{{ route('equipe.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">{{ __('Cancelar') }}</a>
                    </div>
                </form>

                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Senha por e-mail') }}</h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Envia ao usuário o mesmo link da página “Esqueci a senha”, para definir uma nova senha.') }}</p>
                    <form method="POST" action="{{ route('equipe.password-reset', $membro) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            {{ __('Enviar link por e-mail') }}
                        </button>
                    </form>
                </div>

                @can('delete', $membro)
                    <div class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-red-700 dark:text-red-300">{{ __('Remover da equipe') }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Esta ação remove o acesso ao sistema. O registro fica guardado no histórico.') }}</p>
                        <form
                            method="POST"
                            action="{{ route('equipe.destroy', $membro) }}"
                            class="mt-3"
                            onsubmit="return confirm(@json(__('Tem certeza? Esta ação não pode ser desfeita.')));"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200 dark:hover:bg-red-950/60"
                            >
                                {{ __('Remover usuário') }}
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
