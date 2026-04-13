@php
    /** @var \App\Models\Cliente|null $cliente */
    /** @var array<string, string> $ufs */
    $c = $cliente ?? null;
    $dataEmissao = old('data_emissao_rg', $c?->data_emissao_rg?->format('Y-m-d') ?? '');
    $tipoDoc = old('tipo_documento', $c?->tipo_documento);
    if ($tipoDoc === null && filled($c?->cpf)) {
        $tipoDoc = strlen(\App\Support\DocumentoBrasil::apenasDigitos($c->cpf)) === 14 ? 'pj' : 'pf';
    }
    $tipoDoc = $tipoDoc ?: 'pf';

    $orgaosDocRg = \App\Support\BrasilOrgaoEmissorDocumento::optionsRg();
    $orgaosDocCnh = \App\Support\BrasilOrgaoEmissorDocumento::optionsCnh();
    $orgaosDocAll = \App\Support\BrasilOrgaoEmissorDocumento::optionsAll();
    $docIdTipo = old('documento_identidade_tipo', $c?->documento_identidade_tipo ?? \App\Support\BrasilOrgaoEmissorDocumento::TIPO_CNH);
    $docIdNumero = old('documento_identidade_numero', $c?->documento_identidade_numero ?? $c?->rg ?? '');
    $docNumeroLabelShort = match ($docIdTipo) {
        'cnh' => __('CNH'),
        'cin' => __('CIN'),
        default => __('RG'),
    };
    $docNumeroPlaceholder = match ($docIdTipo) {
        'cnh' => __('Número da CNH'),
        'cin' => __('CPF (CIN)'),
        default => __('Número do RG'),
    };
    $nacionalidadesOpts = \App\Support\NacionalidadesComuns::options();
    $orgaoVal = old('orgao_emissor', $c?->orgao_emissor ?? '');
    $nacVal = old('nacionalidade', $c?->nacionalidade ?? '');
    $cidadeVal = old('cidade', $c?->cidade ?? '');
    $natVal = old('naturalidade', $c?->naturalidade ?? '');
    $bairroVal = old('bairro', $c?->bairro ?? '');
    $dataNascVal = old('data_nascimento', $c?->data_nascimento?->format('Y-m-d') ?? '');
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label
            for="nome"
            class="font-semibold text-slate-800 dark:text-slate-200"
            data-nome-label
            data-label-pf="{{ __('Nome completo') }}"
            data-label-pj="{{ __('Razão social') }}"
        >
            {{ __('Nome completo') }} <span class="text-red-600">*</span>
        </x-input-label>
        <x-text-input
            id="nome"
            name="nome"
            class="mt-1 block w-full"
            :value="old('nome', $c?->nome)"
            required
            autofocus
            data-ph-pf="{{ __('Ex.: João da Silva') }}"
            data-ph-pj="{{ __('Ex.: Empresa Exemplo Ltda') }}"
            placeholder="{{ __('Ex.: João da Silva') }}"
        />
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Tipo de documento principal') }} <span class="text-red-600">*</span>
        </span>
        <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-700 dark:text-slate-300">
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input type="radio" name="tipo_documento" value="pf" class="rounded-full border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($tipoDoc === 'pf') required />
                <span>{{ __('CPF') }}</span>
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input type="radio" name="tipo_documento" value="pj" class="rounded-full border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($tipoDoc === 'pj') />
                <span>{{ __('CNPJ') }}</span>
            </label>
        </div>
        <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
    </div>

    <div data-doc-identidade-bloco="1">
        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Documento informado') }} <span class="text-red-600">*</span>
        </span>
        <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-700 dark:text-slate-300">
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input
                    type="radio"
                    name="documento_identidade_tipo"
                    value="cnh"
                    class="rounded-full border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    @checked($docIdTipo === 'cnh')
                />
                <span>{{ __('CNH') }}</span>
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input
                    type="radio"
                    name="documento_identidade_tipo"
                    value="rg"
                    class="rounded-full border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    @checked($docIdTipo === 'rg')
                />
                <span>{{ __('RG') }}</span>
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input
                    type="radio"
                    name="documento_identidade_tipo"
                    value="cin"
                    class="rounded-full border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    @checked($docIdTipo === 'cin')
                />
                <span>{{ __('CIN (Novo RG)') }}</span>
            </label>
        </div>
        <x-input-error :messages="$errors->get('documento_identidade_tipo')" class="mt-2" />
    </div>

    <div>
        <x-input-label
            for="cpf"
            class="font-semibold text-slate-800 dark:text-slate-200"
            data-doc-principal-label
            data-label-cpf="{{ __('CPF') }}"
            data-label-cnpj="{{ __('CNPJ') }}"
        >
            {{ __('CPF') }} <span class="text-red-600">*</span>
        </x-input-label>
        <x-text-input
            id="cpf"
            name="cpf"
            class="mt-1 block w-full"
            :value="old('cpf', $c?->cpf)"
            required
            maxlength="18"
            inputmode="numeric"
            autocomplete="off"
            data-ph-cpf="000.000.000-00"
            data-ph-cnpj="00.000.000/0000-00"
            placeholder="000.000.000-00"
        />
        <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
    </div>

    <div data-doc-identidade-bloco="1">
        <x-input-label
            for="documento_identidade_numero"
            class="font-semibold text-slate-800 dark:text-slate-200"
            data-doc-numero-label
            data-label-rg="{{ __('RG') }}"
            data-label-cnh="{{ __('CNH') }}"
            data-label-cin="{{ __('CIN') }}"
        >
            {{ $docNumeroLabelShort }} <span class="text-red-600">*</span>
        </x-input-label>
        <x-text-input
            id="documento_identidade_numero"
            name="documento_identidade_numero"
            class="mt-1 block w-full"
            :value="$docIdNumero"
            maxlength="40"
            autocomplete="off"
            data-ph-rg="{{ __('Número do RG') }}"
            data-ph-cnh="{{ __('Número da CNH') }}"
            data-ph-cin="{{ __('CPF (CIN)') }}"
            placeholder="{{ $docNumeroPlaceholder }}"
        />
        <x-input-error :messages="$errors->get('documento_identidade_numero')" class="mt-2" />
    </div>

    <div class="hidden" data-orgao-emissor-opts="1" data-opts-rg='@json($orgaosDocRg)' data-opts-cnh='@json($orgaosDocCnh)'></div>

    <div data-doc-identidade-bloco="1">
        <x-input-label for="orgao_emissor" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Órgão emissor (RG/CNH)') }} <span class="text-red-600">*</span>
        </x-input-label>
        <select
            id="orgao_emissor"
            name="orgao_emissor"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
            <option value="" disabled hidden @selected($orgaoVal === '')>{{ __('Selecione') }}</option>
            @foreach ($orgaosDocAll as $val => $label)
                <option value="{{ $val }}" @selected($orgaoVal === $val)>{{ $label }}</option>
            @endforeach
            @if (filled($orgaoVal) && ! array_key_exists($orgaoVal, $orgaosDocAll))
                <option value="{{ $orgaoVal }}" selected>{{ $orgaoVal }}</option>
            @endif
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Órgão emissor do documento (RG ou CNH).') }}</p>
        <x-input-error :messages="$errors->get('orgao_emissor')" class="mt-2" />
    </div>

    <div data-doc-identidade-bloco="1">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="data_emissao_rg" class="font-semibold text-slate-800 dark:text-slate-200">
                    {{ __('Emissão (RG/CNH)') }} <span class="text-red-600">*</span>
                </x-input-label>
                <input
                    id="data_emissao_rg"
                    name="data_emissao_rg"
                    type="text"
                    inputmode="numeric"
                    maxlength="10"
                    autocomplete="off"
                    placeholder="dd/mm/aaaa"
                    value="{{ $dataEmissao ? \Carbon\Carbon::parse($dataEmissao)->format('d/m/Y') : '' }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                />
                <x-input-error :messages="$errors->get('data_emissao_rg')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="validade_cnh" class="font-semibold text-slate-800 dark:text-slate-200">
                    {{ __('Validade (RG/CNH)') }}
                </x-input-label>
                <input
                    id="validade_cnh"
                    name="validade_cnh"
                    type="text"
                    inputmode="numeric"
                    maxlength="10"
                    autocomplete="off"
                    placeholder="dd/mm/aaaa"
                    value="{{ old('validade_cnh', $c?->validade_cnh?->format('d/m/Y') ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                />
                <x-input-error :messages="$errors->get('validade_cnh')" class="mt-2" />
            </div>
        </div>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Datas do documento informado (RG ou CNH).') }}</p>
    </div>

    <div>
        <x-input-label for="data_nascimento" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Data de nascimento') }}
        </x-input-label>
        <input
            id="data_nascimento"
            name="data_nascimento"
            type="text"
            inputmode="numeric"
            maxlength="10"
            autocomplete="off"
            placeholder="dd/mm/aaaa"
            value="{{ $dataNascVal ? \Carbon\Carbon::parse($dataNascVal)->format('d/m/Y') : '' }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        />
        <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nacionalidade" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Nacionalidade') }} <span class="text-red-600">*</span>
        </x-input-label>
        <select
            id="nacionalidade"
            name="nacionalidade"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
            <option value="" disabled hidden @selected($nacVal === '')>{{ __('Selecione') }}</option>
            @foreach ($nacionalidadesOpts as $val => $label)
                <option value="{{ $val }}" @selected($nacVal === $val)>{{ $label }}</option>
            @endforeach
            @if (filled($nacVal) && ! array_key_exists($nacVal, $nacionalidadesOpts))
                <option value="{{ $nacVal }}" selected>{{ $nacVal }}</option>
            @endif
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Preenchido automaticamente a partir do CPF (pessoa física), se aplicável.') }}</p>
        <x-input-error :messages="$errors->get('nacionalidade')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('E-mail') }} <span class="text-red-600">*</span>
        </x-input-label>
        <x-text-input
            id="email"
            name="email"
            type="email"
            class="mt-1 block w-full"
            :value="old('email', $c?->email)"
            required
            maxlength="255"
            placeholder="nome@dominio.com"
        />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="naturalidade" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Naturalidade') }} <span class="text-red-600">*</span>
        </x-input-label>
        <select
            id="naturalidade"
            name="naturalidade"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
            <option value="" disabled hidden @selected($natVal === '')>{{ __('Selecione o município') }}</option>
            @if (filled($natVal))
                <option value="{{ $natVal }}" selected>{{ $natVal }}</option>
            @endif
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Lista conforme a UF; capital sugerida a partir do CPF.') }}</p>
        <x-input-error :messages="$errors->get('naturalidade')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="celular" class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Celular') }}</x-input-label>
        <x-text-input id="celular" name="celular" type="tel" class="mt-1 block w-full" :value="old('celular', $c?->celular)" maxlength="15" autocomplete="tel" placeholder="(00) 00000-0000" inputmode="numeric" />
        <x-input-error :messages="$errors->get('celular')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefone" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Telefone') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <x-text-input id="telefone" name="telefone" type="tel" class="mt-1 block w-full" :value="old('telefone', $c?->telefone)" required maxlength="15" autocomplete="tel" placeholder="(00) 0000-0000" inputmode="numeric" />
        <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cep" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('CEP') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <x-text-input id="cep" name="cep" class="mt-1 block w-full" :value="old('cep', $c?->cep)" required maxlength="9" inputmode="numeric" placeholder="00000-000" />
        <x-input-error :messages="$errors->get('cep')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="endereco" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Endereço') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <x-text-input id="endereco" name="endereco" class="mt-1 block w-full" :value="old('endereco', $c?->endereco)" required maxlength="255" />
        <x-input-error :messages="$errors->get('endereco')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="numero" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Número') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <x-text-input id="numero" name="numero" class="mt-1 block w-full" :value="old('numero', $c?->numero)" required maxlength="20" />
        <x-input-error :messages="$errors->get('numero')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="complemento" class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Complemento') }}</x-input-label>
        <x-text-input id="complemento" name="complemento" class="mt-1 block w-full" :value="old('complemento', $c?->complemento)" maxlength="120" />
        <x-input-error :messages="$errors->get('complemento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="apartamento" class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Apartamento') }}</x-input-label>
        <x-text-input id="apartamento" name="apartamento" class="mt-1 block w-full" :value="old('apartamento', $c?->apartamento)" maxlength="50" />
        <x-input-error :messages="$errors->get('apartamento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="uf" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('UF') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <select
            id="uf"
            name="uf"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
            <option value="" disabled @selected(old('uf', $c?->uf ?? '') === '')>{{ __('Selecione seu estado') }}</option>
            @foreach ($ufs as $sigla => $nomeEstado)
                <option value="{{ $sigla }}" @selected(old('uf', $c?->uf ?? '') === $sigla)>{{ $sigla }} — {{ $nomeEstado }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('uf')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cidade" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Cidade') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <select
            id="cidade"
            name="cidade"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
            <option value="" disabled hidden @selected($cidadeVal === '')>{{ __('Selecione o município') }}</option>
            @if (filled($cidadeVal))
                <option value="{{ $cidadeVal }}" selected>{{ $cidadeVal }}</option>
            @endif
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Lista conforme a UF; capital sugerida a partir do CPF.') }}</p>
        <x-input-error :messages="$errors->get('cidade')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="bairro" class="font-semibold text-slate-800 dark:text-slate-200">
            {{ __('Bairro') }} <span class="text-red-600" aria-hidden="true">•</span><span class="sr-only">{{ __('obrigatório') }}</span>
        </x-input-label>
        <select
            id="bairro"
            name="bairro"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
            <option value="" @selected($bairroVal === '')>{{ __('Selecione o bairro') }}</option>
            @if (filled($bairroVal))
                <option value="{{ $bairroVal }}" selected>{{ $bairroVal }}</option>
            @endif
            <option value="__outro">{{ __('Outro bairro (digite)') }}</option>
        </select>
        <input
            type="text"
            id="bairro_outro"
            maxlength="120"
            class="mt-2 hidden w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
            placeholder="{{ __('Nome do bairro') }}"
            autocomplete="off"
        />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Opções conforme a cidade; ou escolha «Outro» e digite.') }}</p>
        <x-input-error :messages="$errors->get('bairro')" class="mt-2" />
    </div>
</div>
