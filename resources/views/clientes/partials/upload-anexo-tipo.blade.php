@props([
    'cliente',
    'tipoCodigo',
    'titulo',
    'descricao' => null,
])
<form
    method="POST"
    action="{{ route('clientes.anexos.store', $cliente) }}"
    enctype="multipart/form-data"
    class="flex h-full flex-col rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50"
    x-data="{
        drag: false,
        setFilesFromDrop(e) {
            const input = this.$refs.fileInput;
            const dt = new DataTransfer();
            for (const f of e.dataTransfer.files) {
                dt.items.add(f);
            }
            input.files = dt.files;
        },
    }"
>
    @csrf
    <input type="hidden" name="tipo_codigo" value="{{ $tipoCodigo }}" />
    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $titulo }}</h4>
    @if ($descricao)
        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $descricao }}</p>
    @endif
    <input type="file" name="arquivos[]" multiple class="hidden" x-ref="fileInput" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" />
    <div
        @dragover.prevent="drag = true"
        @dragleave.prevent="drag = false"
        @drop.prevent="drag = false; setFilesFromDrop($event)"
        :class="drag ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : 'border-slate-200 dark:border-slate-600'"
        class="mt-3 flex flex-1 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-2 py-5 text-center text-xs text-slate-600 dark:text-slate-400"
        @click="$refs.fileInput.click()"
    >
        {{ __('Arraste ou clique para selecionar') }}
    </div>
    <x-primary-button type="submit" class="mt-3 w-full justify-center !py-2 text-xs">{{ __('Enviar') }}</x-primary-button>
</form>
