@php
    /** @var \App\Models\Empresa|null $empresa */
    $cidadeVal = old('cidade', $empresa?->cidade ?? '');
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="platform_empresa_razao_social">{{ __('Razão social') }}</label>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Denominação registrada na Receita Federal (pessoa jurídica).') }}</p>
        <input id="platform_empresa_razao_social" name="nome" value="{{ old('nome', $empresa?->nome ?? '') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="platform_empresa_nome_fantasia">{{ __('Nome fantasia') }}</label>
        <input
            id="platform_empresa_nome_fantasia"
            name="nome_fantasia"
            type="text"
            value="{{ old('nome_fantasia', $empresa?->nome_fantasia ?? '') }}"
            maxlength="255"
            autocomplete="organization"
            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        />
        @error('nome_fantasia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="platform_empresa_cnpj">{{ __('CNPJ') }}</label>
        <input
            id="platform_empresa_cnpj"
            name="cnpj"
            type="text"
            value="{{ old('cnpj', $empresa?->cnpj ?? '') }}"
            inputmode="numeric"
            autocomplete="off"
            maxlength="18"
            placeholder="00.000.000/0000-00"
            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        />
        @error('cnpj')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="platform_empresa_email_contato">{{ __('E-mail de contacto') }}</label>
        <input
            id="platform_empresa_email_contato"
            type="email"
            name="email_contato"
            value="{{ old('email_contato', $empresa?->email_contato ?? '') }}"
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
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="platform_empresa_telefone">{{ __('Telefone') }}</label>
        <input
            id="platform_empresa_telefone"
            name="telefone"
            type="tel"
            value="{{ old('telefone', $empresa?->telefone ?? '') }}"
            inputmode="numeric"
            autocomplete="tel"
            maxlength="15"
            placeholder="(00) 00000-0000"
            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        />
        @error('telefone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('Endereço') }}</p>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Dados de localização da sede.') }}</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_cep">{{ __('CEP') }}</label>
            <input
                id="empresa_cep"
                name="cep"
                type="text"
                value="{{ old('cep', $empresa?->cep ?? '') }}"
                inputmode="numeric"
                autocomplete="postal-code"
                maxlength="9"
                placeholder="00000-000"
                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            />
            @error('cep')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2 lg:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_endereco">{{ __('Logradouro') }}</label>
            <input
                id="empresa_endereco"
                name="endereco"
                type="text"
                value="{{ old('endereco', $empresa?->endereco ?? '') }}"
                maxlength="255"
                autocomplete="street-address"
                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            />
            @error('endereco')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_numero">{{ __('Número') }}</label>
            <input
                id="empresa_numero"
                name="numero"
                type="text"
                value="{{ old('numero', $empresa?->numero ?? '') }}"
                maxlength="32"
                autocomplete="off"
                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            />
            @error('numero')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_complemento">{{ __('Complemento') }}</label>
            <input
                id="empresa_complemento"
                name="complemento"
                type="text"
                value="{{ old('complemento', $empresa?->complemento ?? '') }}"
                maxlength="120"
                autocomplete="off"
                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            />
            @error('complemento')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="empresa_bairro">{{ __('Bairro') }}</label>
        <input
            id="empresa_bairro"
            name="bairro"
            type="text"
            value="{{ old('bairro', $empresa?->bairro ?? '') }}"
            maxlength="120"
            autocomplete="off"
            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        />
        @error('bairro')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="uf">{{ __('UF') }}</label>
            <select
                id="uf"
                name="uf"
                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
                <option value="" @selected(old('uf', $empresa?->uf ?? '') === '')>{{ __('Selecione seu estado') }}</option>
                @foreach ($ufs as $sigla => $nomeEstado)
                    <option value="{{ $sigla }}" @selected(old('uf', $empresa?->uf ?? '') === $sigla)>{{ $sigla }} — {{ $nomeEstado }}</option>
                @endforeach
            </select>
            @error('uf')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="cidade">{{ __('Cidade') }}</label>
            <select
                id="cidade"
                name="cidade"
                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
                <option value="" disabled hidden @selected($cidadeVal === '')>{{ __('Selecione o município') }}</option>
                @if (filled($cidadeVal))
                    <option value="{{ $cidadeVal }}" selected>{{ $cidadeVal }}</option>
                @endif
            </select>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Lista conforme a UF (IBGE), igual ao cadastro de clientes.') }}</p>
            @error('cidade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Logótipo') }}</label>
        @if ($empresa && $empresa->logo_path)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Ficheiro atual:') }} {{ $empresa->logo_path }}</p>
        @endif
        <input type="file" name="logo" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
