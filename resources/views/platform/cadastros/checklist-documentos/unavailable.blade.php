<x-platform-layout :title="__('Checklist de documentos (global)')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Checklist de documentos (global)') }}</h2>
    </x-slot>

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
        {{ __('Catálogo indisponível: não há empresa na base ou defina NORTEX_PLATFORM_CHECKLIST_EMPRESA_ID no .env.') }}
    </div>
</x-platform-layout>
