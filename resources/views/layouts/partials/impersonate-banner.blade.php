@php
    $impersonatorId = (int) session('impersonator_id', 0);
    $impersonatedId = (int) session('impersonated_user_id', 0);
@endphp

@if ($impersonatorId > 0)
    <div class="border-b border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-100">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 py-2 text-sm">
            <div class="flex items-center gap-2">
                <span class="font-semibold">{{ __('Impersonate ativo') }}</span>
                <span class="text-amber-700 dark:text-amber-200">
                    {{ __('(impersonator_id=:a, user_id=:u)', ['a' => $impersonatorId, 'u' => $impersonatedId ?: Auth::id()]) }}
                </span>
            </div>
            <form method="POST" action="{{ route('platform.impersonate.stop') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white hover:bg-amber-500">
                    {{ __('Sair do impersonate') }}
                </button>
            </form>
        </div>
    </div>
@endif

