@php
    $u = Auth::user();
    $empresaNome = $u?->empresa?->nome;
    $empresaLogoPath = $u?->empresa?->logo_path;
    $empresaLogoUrl = $empresaLogoPath ? route('admin.empresa.logo') : null;
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200/80 bg-white shadow-sm transition-all duration-200 dark:border-slate-800 dark:bg-slate-800"
    :class="[
        sidebarCollapsed ? 'w-[4.5rem]' : 'w-64',
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
>
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-200/80 px-4 dark:border-slate-800" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0" @click="mobileOpen = false">
            @if ($empresaLogoUrl)
                <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <img
                        id="tenant-sidebar-logo"
                        src="{{ $empresaLogoUrl }}"
                        alt="{{ $empresaNome ?: 'Logo da empresa' }}"
                        class="h-full w-full object-contain"
                    />
                </span>
            @else
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4z" />
                    </svg>
                </span>
            @endif
            <span x-show="!sidebarCollapsed" x-cloak class="min-w-0 flex-1">
                <span class="block truncate text-sm font-bold tracking-tight text-slate-900 dark:text-white">NorteX</span>
                <span class="block truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $empresaNome ?: __('Consultoria Naval') }}</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <x-sidebar-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
            </x-slot>
            {{ __('Dashboard') }}
        </x-sidebar-nav-link>

        @if ($u->isPlatformAdmin())
            <x-sidebar-nav-link :href="route('platform.dashboard')" :active="request()->routeIs('platform.*')">
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Z" /></svg>
                </x-slot>
                {{ __('Plataforma') }}
            </x-sidebar-nav-link>
        @endif

        @if ($tenantPlanoAtivo ?? true)
        @if ($u->hasPermission('clientes.view'))
            <x-sidebar-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                </x-slot>
                {{ __('Clientes') }}
            </x-sidebar-nav-link>
        @endif

        @if ($u->hasPermission('embarcacoes.view'))
            <x-sidebar-nav-link :href="route('embarcacoes.index')" :active="request()->routeIs('embarcacoes.*')">
                <x-slot name="icon">
                    @include('embarcacoes.partials.icon-embarcacoes-nav', ['svgClass' => 'h-5 w-5'])
                </x-slot>
                {{ __('Embarcações') }}
            </x-sidebar-nav-link>
        @endif

        @if ($u->hasPermission('habilitacoes.view'))
            <x-sidebar-nav-link :href="route('habilitacoes.index')" :active="request()->routeIs('habilitacoes.*')" :title="__('Habilitações')">
                <x-slot name="icon">
                    @include('habilitacoes.partials.icon-cha-menu', ['svgClass' => 'h-5 w-5'])
                </x-slot>
                {{ __('Habilitações') }}
            </x-sidebar-nav-link>
        @endif

        @if ($u->hasPermission('processos.view'))
            <x-sidebar-nav-link :href="route('processos.index')" :active="request()->routeIs('processos.*')">
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                </x-slot>
                {{ __('Processos') }}
            </x-sidebar-nav-link>
        @endif

        @if ($u->hasPermission('empresa.manage') || $u->hasPermission('usuarios.manage') || $u->hasPermission('auditoria.view'))
            <div
                class="pt-2 mt-1 border-t border-slate-200/80 dark:border-slate-800"
                x-show="!sidebarCollapsed"
                x-cloak
            >
                <p class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Administração') }}</p>
            </div>
        @endif

        @php
            $empresaSectionActive = request()->routeIs('admin.empresa.*') || request()->routeIs('equipe.*') || request()->routeIs('admin.auditoria.*');
        @endphp
        @if ($u->hasPermission('empresa.manage') || $u->hasPermission('usuarios.manage') || $u->hasPermission('auditoria.view'))
            <div class="space-y-1" x-data="{ open: {{ $empresaSectionActive ? 'true' : 'false' }} }">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-slate-100 dark:hover:bg-slate-800"
                    :class="[
                        sidebarCollapsed ? 'justify-center px-2' : '',
                        {{ $empresaSectionActive ? 'true' : 'false' }} ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300',
                    ]"
                    @click="open = !open"
                >
                    <span class="shrink-0 flex h-5 w-5 items-center justify-center text-slate-500">
                        <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Z" /></svg>
                    </span>
                    <span x-show="!sidebarCollapsed" x-cloak class="flex-1 truncate">{{ __('Empresa') }}</span>
                    <span x-show="!sidebarCollapsed" x-cloak class="shrink-0 text-slate-400" :class="open ? 'rotate-180' : ''">
                        <svg class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </span>
                </button>

                <div class="space-y-1 pl-6" x-show="open && !sidebarCollapsed" x-cloak>
                    @if ($u->hasPermission('empresa.manage'))
                        <a href="{{ route('admin.empresa.edit') }}" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.empresa.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ __('Dados') }}
                        </a>
                    @endif
                    @if ($u->hasPermission('usuarios.manage'))
                        <a href="{{ route('equipe.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('equipe.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ __('Equipe') }}
                        </a>
                    @endif
                    @if ($u->hasPermission('auditoria.view'))
                        <a href="{{ route('admin.auditoria.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.auditoria.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ __('Auditoria') }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if ($u->hasPermission('aulas.view'))
            <x-sidebar-nav-link :href="route('aulas.index')" :active="request()->routeIs('aulas.*')">
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.716 50.716 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6.75 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" /></svg>
                </x-slot>
                {{ __('Escola Náutica') }}
            </x-sidebar-nav-link>
        @else
            <div
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-400 dark:text-slate-600 cursor-not-allowed"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                title="{{ __('Módulo em desenvolvimento') }}"
            >
                <span class="shrink-0 flex h-5 w-5 items-center justify-center opacity-70">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.716 50.716 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6.75 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" /></svg>
                </span>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Escola Náutica') }}</span>
            </div>
        @endif

        @if ($u->hasPermission('financeiro.view'))
            <x-sidebar-nav-link :href="route('financeiro.index')" :active="request()->routeIs('financeiro.*')">
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </x-slot>
                {{ __('Financeiro') }}
            </x-sidebar-nav-link>
        @else
            <div
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-400 dark:text-slate-600 cursor-not-allowed"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                title="{{ __('Sem acesso ao módulo financeiro') }}"
            >
                <span class="shrink-0 flex h-5 w-5 items-center justify-center opacity-70">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </span>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Financeiro') }}</span>
            </div>
        @endif
        @endif
    </nav>

    <div class="mt-auto space-y-1 border-t border-slate-200/80 p-3 dark:border-slate-800">
        <button
            type="button"
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            :class="sidebarCollapsed ? 'justify-center px-2' : ''"
            @click="$store.theme.toggle()"
        >
            <span class="shrink-0 flex h-5 w-5 items-center justify-center text-slate-500 dark:text-slate-400">
                <svg class="hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                <svg class="dark:hidden" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
            </span>
            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Modo escuro') }}</span>
        </button>

        @if ($u->empresa_id)
            <x-sidebar-nav-link
                :href="route('planos.index')"
                :active="request()->routeIs('planos.*')"
                :badge-text="($tenantPlanoAtivo ?? false) ? __('Ativo') : __('Pendente')"
                :badge-tone="($tenantPlanoAtivo ?? false) ? 'success' : 'warning'"
                @click="mobileOpen = false"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 15h19.5m-16.5-5.25h6m-6 6.75h6m-1.5-13.5h3a3 3 0 0 1 3 3v13.5a3 3 0 0 1-3 3h-3m-6-19.5h3a3 3 0 0 1 3 3v13.5a3 3 0 0 1-3 3h-3m0-19.5v19.5" /></svg>
                </x-slot>
                {{ __('Plano') }}
            </x-sidebar-nav-link>
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
