<x-app-layout title="{{ __('Empresa') }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Dados da empresa') }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.empresa.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                        <input name="nome" value="{{ old('nome', $empresa->nome) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_cnpj">{{ __('CNPJ') }}</label>
                        <input
                            id="empresa_cnpj"
                            name="cnpj"
                            type="text"
                            value="{{ old('cnpj', $empresa->cnpj) }}"
                            inputmode="numeric"
                            autocomplete="off"
                            maxlength="18"
                            placeholder="00.000.000/0000-00"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('cnpj')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_email_contato">{{ __('E-mail de contacto') }}</label>
                        <input
                            id="empresa_email_contato"
                            type="email"
                            name="email_contato"
                            value="{{ old('email_contato', $empresa->email_contato) }}"
                            autocomplete="email"
                            inputmode="email"
                            maxlength="255"
                            placeholder="contato@empresa.com"
                            spellcheck="false"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('email_contato')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_telefone">{{ __('Telefone') }}</label>
                        <input
                            id="empresa_telefone"
                            name="telefone"
                            type="tel"
                            value="{{ old('telefone', $empresa->telefone) }}"
                            inputmode="numeric"
                            autocomplete="tel"
                            maxlength="15"
                            placeholder="(00) 00000-0000"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />
                        @error('telefone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Logótipo') }}</label>
                        @if ($empresa->logo_path)
                            <p class="mt-1 text-xs text-slate-500">{{ __('Ficheiro atual:') }} {{ $empresa->logo_path }}</p>
                        @endif
                        <input type="file" name="logo" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('O slug da empresa (:s) não é alterado aqui por segurança.', ['s' => $empresa->slug]) }}</p>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('admin.empresa.compromissos.index') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Compromissos da agenda') }}</a>
                    <span class="text-slate-500"> — </span>
                    {{ __('reuniões e dias de atendimento na Marinha no dashboard.') }}
                </p>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const onlyDigits = (value) => String(value || '').replace(/\D/g, '');

            const formatCnpj = (value) => {
                const d = onlyDigits(value).slice(0, 14);
                if (d.length <= 2) return d;
                if (d.length <= 5) return `${d.slice(0, 2)}.${d.slice(2)}`;
                if (d.length <= 8) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5)}`;
                if (d.length <= 12) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8)}`;
                return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8, 12)}-${d.slice(12)}`;
            };

            const formatPhoneBr = (value) => {
                const d = onlyDigits(value).slice(0, 11);
                if (d.length === 0) return '';
                if (d.length <= 2) return `(${d}`;
                if (d.length <= 6) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
                if (d.length <= 10) return `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
                return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
            };

            const bindMask = (id, formatter) => {
                const input = document.getElementById(id);
                if (!input) return;
                const apply = () => {
                    const formatted = formatter(input.value);
                    if (formatted !== input.value) {
                        input.value = formatted;
                    }
                };
                input.addEventListener('input', apply);
                input.addEventListener('blur', apply);
                apply();
            };

            bindMask('empresa_cnpj', formatCnpj);
            bindMask('empresa_telefone', formatPhoneBr);
        })();
    </script>
</x-app-layout>
