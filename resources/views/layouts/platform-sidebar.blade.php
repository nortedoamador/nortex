@php
    $u = Auth::user();
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200/80 bg-white shadow-sm transition-all duration-200 dark:border-slate-800 dark:bg-slate-800"
    :class="[
        sidebarCollapsed ? 'w-[4.5rem]' : 'w-64',
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
>
    @if (\App\Support\PlatformMaintenance::enabled())
        <div class="border-b border-amber-200/80 bg-amber-50 px-3 py-2 text-[11px] font-semibold leading-snug text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/50 dark:text-amber-100" x-show="!sidebarCollapsed" x-cloak>
            {{ __('Manutenção ativa') }}
        </div>
    @endif
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-200/80 px-4 dark:border-slate-800" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
        <a href="{{ route('platform.dashboard') }}" class="flex min-w-0 items-center gap-2" @click="mobileOpen = false">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4z" />
                </svg>
            </span>
            <span x-show="!sidebarCollapsed" x-cloak class="min-w-0 flex-1">
                <span class="block truncate text-sm font-bold tracking-tight text-slate-900 dark:text-white">NorteX</span>
                <span class="block truncate text-[11px] text-slate-500 dark:text-slate-400">{{ __('Plataforma') }}</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <x-sidebar-nav-link :href="route('platform.dashboard')" :active="request()->routeIs('platform.dashboard')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
            </x-slot>
            {{ __('Visão geral') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.empresas.index')" :active="request()->routeIs('platform.empresas.*') && ! request()->routeIs('platform.empresas.admin.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Z" /></svg>
            </x-slot>
            {{ __('Empresas') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.assinaturas.index')" :active="request()->routeIs('platform.assinaturas.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3 3.75h12.75m-12.75 0v1.5c0 1.243 1.007 2.25 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-1.5m-13.5-9V6a2.25 2.25 0 0 1 2.25-2.25h9A2.25 2.25 0 0 1 21 6v1.5m-18 0h18" /></svg>
            </x-slot>
            {{ __('Assinaturas') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.usuarios.index')" :active="request()->routeIs('platform.usuarios.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            </x-slot>
            {{ __('Utilizadores') }}
        </x-sidebar-nav-link>

        <div class="pt-2 mt-1 border-t border-slate-200/80 dark:border-slate-800" x-show="!sidebarCollapsed" x-cloak>
            <p class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Catálogo global') }}</p>
        </div>

        <x-sidebar-nav-link :href="route('platform.cadastros.tipos-servico.index')" :active="request()->routeIs('platform.cadastros.tipos-servico.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m-9 0h12A2.25 2.25 0 0 0 20.25 15.75V8.25A2.25 2.25 0 0 0 18 6H6A2.25 2.25 0 0 0 3.75 8.25v7.5A2.25 2.25 0 0 0 6 18Z" /></svg>
            </x-slot>
            {{ __('Tipos de serviço') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.cadastros.tipos-processo.index')" :active="request()->routeIs('platform.cadastros.tipos-processo.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
            </x-slot>
            {{ __('Tipos de processo') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.cadastros.checklist-documentos.index')" :active="request()->routeIs('platform.cadastros.checklist-documentos.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
            </x-slot>
            {{ __('Checklist de documentos') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.cadastros.documentos-automatizados.index')" :active="request()->routeIs('platform.cadastros.documentos-automatizados.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            </x-slot>
            {{ __('Docs automatizados') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link :href="route('platform.cadastros.anexo-tipos.index')" :active="request()->routeIs('platform.cadastros.anexo-tipos.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739 10.682 20.43a4.5 4.5 0 1 1-6.364-6.364l9.193-9.193a3 3 0 1 1 4.243 4.243l-9.194 9.193a1.5 1.5 0 0 1-2.121-2.121l8.133-8.132" /></svg>
            </x-slot>
            {{ __('Tipos de anexo') }}
        </x-sidebar-nav-link>

        <div class="pt-2 mt-1 border-t border-slate-200/80 dark:border-slate-800" x-show="!sidebarCollapsed" x-cloak>
            <p class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Operação') }}</p>
        </div>

        <x-sidebar-nav-link :href="route('platform.auditoria.index')" :active="request()->routeIs('platform.auditoria.*')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </x-slot>
            {{ __('Auditoria') }}
        </x-sidebar-nav-link>
    </nav>

    <div class="mt-auto space-y-1 border-t border-slate-200/80 p-3 dark:border-slate-800">
        @if ($u?->empresa_id)
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                @click="mobileOpen = false"
            >
                <span class="shrink-0 flex h-5 w-5 items-center justify-center text-slate-500">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 19.5 12 12l7.5-7.5" /></svg>
                </span>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Voltar para aplicação') }}</span>
            </a>
        @endif

        <a
            href="{{ route('profile.edit') }}"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            :class="sidebarCollapsed ? 'justify-center px-2' : ''"
            @click="mobileOpen = false"
        >
            <span class="shrink-0 flex h-5 w-5 items-center justify-center text-slate-500">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.37.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            </span>
            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Configurações') }}</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-700 dark:text-slate-300 dark:hover:bg-red-950/40 dark:hover:text-red-300"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
            >
                <span class="shrink-0 flex h-5 w-5 items-center justify-center">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
                </span>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Sair') }}</span>
            </button>
        </form>

        <button
            type="button"
            class="hidden lg:flex w-full items-center justify-center rounded-lg border border-slate-200 py-2 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
            @click="sidebarCollapsed = !sidebarCollapsed"
            :title="sidebarCollapsed ? '{{ __('Expandir menu') }}' : '{{ __('Recolher menu') }}'"
        >
            <svg class="h-5 w-5 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
            </svg>
        </button>
    </div>
</aside>
