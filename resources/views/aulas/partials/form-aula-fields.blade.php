@php
    /** @var string $idPrefix */
    $pf = $idPrefix ?? '';
    /** @var bool $isEdit */
    /** @var \App\Models\AulaNautica|null $aula */
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Dados da Aula') }}</h3>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="{{ $pf }}numero_oficio" :value="__('Número do Ofício')" />
            <x-text-input id="{{ $pf }}numero_oficio" name="numero_oficio" class="mt-1 block w-full" required :value="old('numero_oficio', $aula?->numero_oficio)" />
            <x-input-error :messages="$errors->get('numero_oficio')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="{{ $pf }}data_aula" :value="__('Data da Aula')" />
            <input id="{{ $pf }}data_aula" name="data_aula" type="date" required value="{{ old('data_aula', $aula?->data_aula?->format('Y-m-d') ?? '') }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
            <x-input-error :messages="$errors->get('data_aula')" class="mt-2" />
        </div>
        <div class="md:col-span-2">
            <x-input-label for="{{ $pf }}local" :value="__('Local')" />
            <x-text-input id="{{ $pf }}local" name="local" class="mt-1 block w-full" required :value="old('local', $aula?->local)" />
            <x-input-error :messages="$errors->get('local')" class="mt-2" />
        </div>
        <div class="md:col-span-2">
            <x-input-label for="{{ $pf }}tipo_aula" :value="__('Tipo da aula')" />
            <select
                id="{{ $pf }}tipo_aula"
                name="tipo_aula"
                required
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
            >
                @foreach (($tiposAula ?? []) as $t)
                    <option value="{{ $t['value'] }}" @selected(old('tipo_aula', $aula?->tipo_aula ?? 'teorica') === $t['value'])>
                        {{ $t['label'] }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('tipo_aula')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="{{ $pf }}hora_inicio" :value="__('Hora início')" />
            <input id="{{ $pf }}hora_inicio" name="hora_inicio" type="time" value="{{ old('hora_inicio', $aula?->hora_inicio ? substr($aula->hora_inicio, 0, 5) : '') }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
            <x-input-error :messages="$errors->get('hora_inicio')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="{{ $pf }}hora_fim" :value="__('Hora fim')" />
            <input id="{{ $pf }}hora_fim" name="hora_fim" type="time" value="{{ old('hora_fim', $aula?->hora_fim ? substr($aula->hora_fim, 0, 5) : '') }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
            <x-input-error :messages="$errors->get('hora_fim')" class="mt-2" />
        </div>
    </div>
</div>

<div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Alunos vinculados') }}</h3>

    <div class="mt-4 grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
            <x-input-label for="{{ $pf }}cpf_aluno" :value="__('Adicionar aluno por CPF')" />
            <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-text-input
                    id="{{ $pf }}cpf_aluno"
                    x-ref="cpfInput"
                    x-model="cpfQ"
                    @input="onCpfInput()"
                    @keydown="onKeydown($event)"
                    @blur="onBlur()"
                    class="block w-full"
                    placeholder="000.000.000-00"
                    autocomplete="off"
                    inputmode="numeric"
                />
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    @click="openNovoAluno()"
                    :disabled="!cpfDigits || cpfDigits.length < 11"
                    :class="(!cpfDigits || cpfDigits.length < 11) ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    {{ __('Cadastrar novo aluno') }}
                </button>
            </div>

            <template x-if="open && sugestões.length">
                <div :style="panelStyle" class="rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <template x-for="(it, idx) in sugestões" :key="it.id">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800"
                            :class="idx === highlighted ? 'bg-slate-50 dark:bg-slate-800' : ''"
                            @mousedown.prevent="pick(it)"
                        >
                            <span class="font-semibold text-slate-900 dark:text-white" x-text="it.nome"></span>
                            <span class="text-slate-600 dark:text-slate-300" x-text="it.cpf"></span>
                        </button>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div class="mt-4 space-y-2">
        <template x-for="aluno in alunos" :key="aluno.id">
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-slate-900 dark:text-white" x-text="aluno.nome"></p>
                    <p class="truncate text-xs text-slate-600 dark:text-slate-300" x-text="aluno.cpf"></p>
                </div>
                <button type="button" class="text-red-600 hover:text-red-700 dark:text-red-400" @click="removeAluno(aluno.id)">{{ __('Remover') }}</button>
                <input type="hidden" name="alunos_ids[]" :value="aluno.id" />
            </div>
        </template>
        <div x-show="alunos.length === 0" class="text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum aluno vinculado ainda.') }}</div>
    </div>
</div>

<div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Instrutores vinculados') }}</h3>

    <div class="mt-4 grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
            <x-input-label for="{{ $pf }}cpf_instrutor" :value="__('Adicionar instrutor por CPF')" />
            <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-text-input
                    id="{{ $pf }}cpf_instrutor"
                    x-ref="cpfInstrutorInput"
                    x-model="cpfInstrutorQ"
                    @input="onCpfInstrutorInput()"
                    @keydown="onInstrutorKeydown($event)"
                    @blur="onInstrutorBlur()"
                    class="block w-full"
                    placeholder="000.000.000-00"
                    autocomplete="off"
                    inputmode="numeric"
                />
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    @click="openNovoInstrutor()"
                    :disabled="!cpfInstrutorDigits || cpfInstrutorDigits.length < 11"
                    :class="(!cpfInstrutorDigits || cpfInstrutorDigits.length < 11) ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    {{ __('Cadastrar novo instrutor') }}
                </button>
            </div>

            <template x-if="openInstrutor && sugestõesInstrutor.length">
                <div :style="panelStyleInstrutor" class="rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <template x-for="(it, idx) in sugestõesInstrutor" :key="it.id">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800"
                            :class="idx === highlightedInstrutor ? 'bg-slate-50 dark:bg-slate-800' : ''"
                            @mousedown.prevent="pickInstrutor(it)"
                        >
                            <span class="min-w-0">
                                <span class="block font-semibold text-slate-900 dark:text-white" x-text="it.nome"></span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400" x-show="it.cha" x-text="it.cha ? ('CHA ' + it.cha) : ''"></span>
                            </span>
                            <span class="shrink-0 text-slate-600 dark:text-slate-300" x-text="it.cpf"></span>
                        </button>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div class="mt-4 space-y-2">
        <template x-for="ins in instrutores" :key="ins.id">
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-slate-900 dark:text-white" x-text="ins.nome"></p>
                    <p class="truncate text-xs text-slate-600 dark:text-slate-300" x-text="ins.cpf"></p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400" x-show="ins.cha" x-text="ins.cha ? ('CHA ' + ins.cha) : ''"></p>
                </div>
                <button type="button" class="shrink-0 text-red-600 hover:text-red-700 dark:text-red-400" @click="removeInstrutor(ins.id)">{{ __('Remover') }}</button>
                <input type="hidden" name="escola_instrutores_ids[]" :value="ins.id" />
            </div>
        </template>
        <div x-show="instrutores.length === 0" class="text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum instrutor vinculado ainda.') }}</div>
    </div>
    <x-input-error :messages="$errors->get('escola_instrutores_ids')" class="mt-2" />
</div>
