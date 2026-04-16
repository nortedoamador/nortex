@php
    /** @var \App\Models\Cliente|null $cliente */
    /** @var bool $nxInstrutorChaAfterPrincipalAnexos Inserir bloco CHA (modal instrutor) após CNH/comprovante e antes de «Outros anexos». */
    $nxInstrutorChaAfterPrincipalAnexos = $nxInstrutorChaAfterPrincipalAnexos ?? false;
    $c = $cliente ?? null;
    $tipoDoc = old('tipo_documento', $c?->tipo_documento);
    if ($tipoDoc === null && filled($c?->cpf)) {
        $tipoDoc = strlen(\App\Support\DocumentoBrasil::apenasDigitos($c->cpf)) === 14 ? 'pj' : 'pf';
    }
    $tipoDoc = $tipoDoc ?: 'pf';

    $msgsAnexo = collect($errors->getMessages())
        ->filter(fn ($_, string $k) => str_starts_with($k, 'anexo_'))
        ->flatten()
        ->values()
        ->all();

    $outroVal = (string) old('anexo_outro_tipo', '');
    $outroPreset = match ($outroVal) {
        \App\Support\ClienteTiposAnexo::CNH => \App\Support\ClienteTiposAnexo::CNH,
        \App\Support\ClienteTiposAnexo::COMPROVANTE_ENDERECO => \App\Support\ClienteTiposAnexo::COMPROVANTE_ENDERECO,
        \App\Support\ClienteTiposAnexo::DOC_REPRESENTANTE_LEGAL => \App\Support\ClienteTiposAnexo::DOC_REPRESENTANTE_LEGAL,
        \App\Support\ClienteTiposAnexo::CARTAO_CNPJ => \App\Support\ClienteTiposAnexo::CARTAO_CNPJ,
        \App\Support\ClienteTiposAnexo::COMPROVANTE_INSCRICAO_ESTADUAL => \App\Support\ClienteTiposAnexo::COMPROVANTE_INSCRICAO_ESTADUAL,
        \App\Support\ClienteTiposAnexo::COMPROVANTE_INSCRICAO_MUNICIPAL => \App\Support\ClienteTiposAnexo::COMPROVANTE_INSCRICAO_MUNICIPAL,
        'RG' => 'RG',
        default => ($outroVal !== '' ? '__outro' : ''),
    };
    $outroCustom = $outroPreset === '__outro' ? $outroVal : '';
@endphp

<div class="border-t border-slate-200 pt-6 dark:border-slate-700">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Documentos para envio') }}</h3>
    <p data-anexo-intro-pf class="mt-1 text-xs text-slate-600 dark:text-slate-400 {{ $tipoDoc === 'pj' ? 'hidden' : '' }}">{{ __('O envio de arquivos é opcional. Quando quiser, anexe CNH e comprovante de endereço (vários arquivos por tipo, ex.: frente e verso). Os arquivos serão guardados ao salvar ou cadastrar.') }}</p>
    <p data-anexo-intro-pj class="mt-1 text-xs text-slate-600 dark:text-slate-400 {{ $tipoDoc === 'pf' ? 'hidden' : '' }}">{{ __('O envio de arquivos é opcional. Quando quiser, anexe o contrato social e o comprovante de endereço (vários ficheiros por tipo). Os ficheiros serão guardados ao salvar ou cadastrar.') }}</p>

    @if (count($msgsAnexo) > 0)
        <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-red-600 dark:text-red-400">
            @foreach ($msgsAnexo as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div
            x-data="{
                drag: false,
                anexoNames: '',
                syncCnhNames() {
                    const el = this.$refs.fileInputCnh;
                    this.anexoNames =
                        el && el.files && el.files.length
                            ? Array.from(el.files)
                                  .map((f) => f.name)
                                  .join(', ')
                            : '';
                },
                setFilesFromDrop(e) {
                    const input = this.$refs.fileInputCnh;
                    const dt = new DataTransfer();
                    for (const f of e.dataTransfer.files) {
                        dt.items.add(f);
                    }
                    input.files = dt.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                },
            }"
            class="flex h-full flex-col rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50"
        >
            <h4 data-anexo-slot1-pf class="text-sm font-semibold text-slate-900 dark:text-white {{ $tipoDoc === 'pj' ? 'hidden' : '' }}">{{ __('CNH (Carteira Nacional de Habilitação)') }}</h4>
            <h4 data-anexo-slot1-pj class="text-sm font-semibold text-slate-900 dark:text-white {{ $tipoDoc === 'pf' ? 'hidden' : '' }}">{{ __('Contrato social') }}</h4>
            <p data-anexo-slot1-pf class="mt-1 text-xs text-slate-600 dark:text-slate-400 {{ $tipoDoc === 'pj' ? 'hidden' : '' }}">{{ __('Frente e verso em um ou mais ficheiros. PDF ou imagem (JPG, PNG, WebP), ou DOC/DOCX.') }}</p>
            <p data-anexo-slot1-pj class="mt-1 text-xs text-slate-600 dark:text-slate-400 {{ $tipoDoc === 'pf' ? 'hidden' : '' }}">{{ __('Um ou mais ficheiros (ex.: integral do contrato). PDF ou imagem (JPG, PNG, WebP), ou DOC/DOCX.') }}</p>
            {{-- input com display:none + .click() falha em vários navegadores; label + sr-only abre o seletor de ficheiros de forma fiável. --}}
            <label
                @dragover.prevent="drag = true"
                @dragleave.prevent="drag = false"
                @drop.prevent="drag = false; setFilesFromDrop($event)"
                :class="drag ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : 'border-slate-200 dark:border-slate-600'"
                class="mt-3 flex flex-1 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-2 py-5 text-center text-xs text-slate-600 dark:text-slate-400"
            >
                <input
                    type="file"
                    name="anexo_cnh[]"
                    multiple
                    class="sr-only"
                    x-ref="fileInputCnh"
                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
                    @change="syncCnhNames()"
                />
                <span>{{ __('Arraste ou clique para selecionar') }}</span>
            </label>
            <p
                x-show="anexoNames !== ''"
                x-text="anexoNames"
                class="mt-2 break-all text-xs font-medium text-emerald-700 dark:text-emerald-300"
            ></p>
        </div>

        <div
            x-data="{
                drag: false,
                anexoNames: '',
                syncCompNames() {
                    const el = this.$refs.fileInputComp;
                    this.anexoNames =
                        el && el.files && el.files.length
                            ? Array.from(el.files)
                                  .map((f) => f.name)
                                  .join(', ')
                            : '';
                },
                setFilesFromDrop(e) {
                    const input = this.$refs.fileInputComp;
                    const dt = new DataTransfer();
                    for (const f of e.dataTransfer.files) {
                        dt.items.add(f);
                    }
                    input.files = dt.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                },
            }"
            class="flex h-full flex-col rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50"
        >
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Comprovante de endereço') }}</h4>
            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Conta de luz, água, telefone, contrato etc., em PDF ou imagem.') }}</p>
            <label
                @dragover.prevent="drag = true"
                @dragleave.prevent="drag = false"
                @drop.prevent="drag = false; setFilesFromDrop($event)"
                :class="drag ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : 'border-slate-200 dark:border-slate-600'"
                class="mt-3 flex flex-1 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-2 py-5 text-center text-xs text-slate-600 dark:text-slate-400"
            >
                <input
                    type="file"
                    name="anexo_comprovante[]"
                    multiple
                    class="sr-only"
                    x-ref="fileInputComp"
                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
                    @change="syncCompNames()"
                />
                <span>{{ __('Arraste ou clique para selecionar') }}</span>
            </label>
            <p
                x-show="anexoNames !== ''"
                x-text="anexoNames"
                class="mt-2 break-all text-xs font-medium text-emerald-700 dark:text-emerald-300"
            ></p>
        </div>
    </div>

    @if ($nxInstrutorChaAfterPrincipalAnexos)
        @include('aulas.partials.instrutor-modal-cha-section')
    @endif

    <div
        class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50"
        x-data="{
            preset: @js($outroPreset),
            custom: @js($outroCustom),
            drag: false,
            anexoNames: '',
            tipoDocumentoSelecionado() {
                const p = (this.preset || '').trim();
                if (! p) {
                    return false;
                }
                if (p === '__outro') {
                    return (this.custom || '').trim() !== '';
                }

                return true;
            },
            syncOutroNames() {
                const el = this.$refs.fileInputOutro;
                this.anexoNames =
                    el && el.files && el.files.length
                        ? Array.from(el.files)
                              .map((f) => f.name)
                              .join(', ')
                        : '';
            },
            setFilesFromDrop(e) {
                if (! this.tipoDocumentoSelecionado()) {
                    return;
                }
                const input = this.$refs.fileInputOutro;
                const dt = new DataTransfer();
                for (const f of e.dataTransfer.files) {
                    dt.items.add(f);
                }
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            },
        }"
    >
        <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Outros anexos') }}</h4>
        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Opcional: selecione o tipo do anexo (ou escolha “Outro”).') }}</p>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">{{ __('Tipo') }}</label>
                <select
                    name="anexo_outro_tipo_preset"
                    data-anexo-outro-preset="1"
                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                    x-model="preset"
                >
                    <option value="" data-scope="both" @selected($outroPreset === '')>{{ __('Selecione…') }}</option>
                    <option value="RG" data-scope="pf">{{ __('RG') }}</option>
                    <option value="{{ \App\Support\ClienteTiposAnexo::CNH }}" data-scope="pf">{{ __('CNH') }}</option>
                    <option value="{{ \App\Support\ClienteTiposAnexo::COMPROVANTE_ENDERECO }}" data-scope="pf">{{ __('Comprovante de endereço') }}</option>
                    <option value="{{ \App\Support\ClienteTiposAnexo::DOC_REPRESENTANTE_LEGAL }}" data-scope="pj">{{ __('Documento do representante legal (RG/CNH)') }}</option>
                    <option value="{{ \App\Support\ClienteTiposAnexo::CARTAO_CNPJ }}" data-scope="pj">{{ __('Cartão CNPJ') }}</option>
                    <option value="{{ \App\Support\ClienteTiposAnexo::COMPROVANTE_INSCRICAO_ESTADUAL }}" data-scope="pj">{{ __('Comprovante de Inscrição Estadual') }}</option>
                    <option value="{{ \App\Support\ClienteTiposAnexo::COMPROVANTE_INSCRICAO_MUNICIPAL }}" data-scope="pj">{{ __('Comprovante de Inscrição Municipal') }}</option>
                    <option value="__outro" data-scope="both">{{ __('Outro anexo (digite)') }}</option>
                </select>
            </div>
            <div x-show="preset === '__outro'" x-cloak>
                <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">{{ __('Tipo do outro anexo') }}</label>
                <input
                    type="text"
                    name="anexo_outro_tipo_custom"
                    x-model="custom"
                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                    maxlength="64"
                    placeholder="{{ __('Ex.: Passaporte') }}"
                />
            </div>
        </div>
        <div class="mt-3">
            <label
                @dragover.prevent="drag = tipoDocumentoSelecionado()"
                @dragleave.prevent="drag = false"
                @drop.prevent="drag = false; setFilesFromDrop($event)"
                :class="{
                    'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40': drag && tipoDocumentoSelecionado(),
                    'border-slate-200 dark:border-slate-600': ! (drag && tipoDocumentoSelecionado()),
                    'pointer-events-none cursor-not-allowed opacity-50': ! tipoDocumentoSelecionado(),
                    'cursor-pointer': tipoDocumentoSelecionado(),
                }"
                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed px-2 py-5 text-center text-xs text-slate-600 dark:text-slate-400"
                @click="tipoDocumentoSelecionado() && $refs.fileInputOutro.click()"
            >
                <input
                    type="file"
                    name="anexo_outro[]"
                    multiple
                    class="sr-only"
                    x-ref="fileInputOutro"
                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
                    @change="syncOutroNames()"
                />
                <span>{{ __('Arraste ou clique para selecionar') }}</span>
            </label>
            <p
                x-show="anexoNames !== ''"
                x-text="anexoNames"
                class="mt-2 break-all text-xs font-medium text-emerald-700 dark:text-emerald-300"
            ></p>
        </div>
    </div>
</div>
