<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' — '.config('app.name', 'NorteX') : __('Plataforma').' — '.config('app.name', 'NorteX') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-slate-50 text-slate-900 font-sans antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-full">
            @include('layouts.partials.impersonate-banner')

            <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('platform.empresas.index') }}" class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">
                            NorteX <span class="text-violet-600 dark:text-violet-400">{{ __('Plataforma') }}</span>
                        </a>
                        <nav class="flex items-center gap-3 text-sm font-medium">
                            <a href="{{ route('platform.dashboard') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 {{ request()->routeIs('platform.dashboard') ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ __('Dashboard') }}</a>
                            <a href="{{ route('platform.empresas.index') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 {{ request()->routeIs('platform.empresas.*') ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ __('Empresas') }}</a>
                            <a href="{{ route('platform.cadastros.tipos-processo.index') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 {{ request()->routeIs('platform.cadastros.*') ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ __('Cadastros') }}</a>
                            <a href="{{ route('platform.auditoria.index') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 {{ request()->routeIs('platform.auditoria.*') ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ __('Auditoria') }}</a>
                            <a href="{{ route('platform.usuarios.index') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 {{ request()->routeIs('platform.usuarios.*') ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ __('Usuários') }}</a>
                            @if (Auth::user()?->empresa_id)
                                <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">{{ __('Aplicação') }}</a>
                            @endif
                        </nav>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('profile.edit') }}" class="text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">{{ __('Perfil') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="font-medium text-red-600 hover:text-red-500 dark:text-red-400">{{ __('Sair') }}</button>
                        </form>
                    </div>
                </div>
            </header>

            @isset($header)
                <div class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
