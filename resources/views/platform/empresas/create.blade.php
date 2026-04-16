<x-platform-layout :title="__('Nova empresa')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Nova empresa') }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Mesmos dados gerais que o cadastro da empresa no painel (razão social, contacto, endereço, logótipo).') }}</p>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <form
            method="POST"
            action="{{ route('platform.empresas.store') }}"
            enctype="multipart/form-data"
            class="space-y-4"
            data-cliente-ficha
            data-capitais='@json(\App\Support\BrasilCapitais::porUf())'
            data-msg-selecione-municipio="{{ __('Selecione o município') }}"
            data-geo-cep="empresa_cep"
            data-geo-endereco="empresa_endereco"
            data-geo-bairro="empresa_bairro"
            data-geo-complemento="empresa_complemento"
        >
            @csrf
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Identificação na plataforma') }}</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }} <span class="text-slate-400">(a-z, números, hífen)</span></label>
                    <input name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="ativo" value="0" />
                        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', true)) class="rounded border-slate-300 text-violet-600" />
                        {{ __('Empresa ativa') }}
                    </label>
                </div>

                <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Dados gerais') }}</h3>
                </div>

                @include('platform.empresas.partials.dados-gerais', ['empresa' => null, 'ufs' => $ufs])
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Criar') }}</button>
                <a href="{{ route('platform.empresas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
</x-platform-layout>
