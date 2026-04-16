<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' — '.config('app.name', 'NorteX') : config('app.name', 'NorteX') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-slate-50 text-slate-900 font-sans antialiased dark:bg-slate-950 dark:text-slate-100" data-turbo="false">
        @if (request()->routeIs('aulas.*'))
            @include('aulas.partials.form-aula-scripts')
        @endif
        <div
            class="min-h-full"
            x-data="{ sidebarCollapsed: false, mobileOpen: false }"
            @keydown.escape.window="mobileOpen = false"
        >
            @include('layouts.sidebar')

            <div
                x-show="mobileOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
                @click="mobileOpen = false"
                style="display: none;"
            ></div>

            <div
                class="min-h-screen transition-[padding] duration-200"
                :class="sidebarCollapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-64'"
            >
                @include('layouts.partials.impersonate-banner')

                <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 lg:hidden">
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="mobileOpen = true"
                        aria-label="{{ __('Abrir menu') }}"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">NorteX</span>
                </header>

                @isset($header)
                    {{-- Não aplicar cor de link a âncoras com fundo índigo (botões no cabeçalho mantêm text-white). --}}
                    <header class="border-b border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900 [&_a:not([class*='bg-indigo-'])]:text-indigo-600 [&_a:not([class*='bg-indigo-'])]:dark:text-indigo-400">
                        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-slate-900 [&_h2]:dark:text-white">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    @if (session('status'))
                        <div class="mx-auto max-w-[1600px] px-4 pt-4 sm:px-6 lg:px-8">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mx-auto max-w-[1600px] px-4 pt-4 sm:px-6 lg:px-8">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100">
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
