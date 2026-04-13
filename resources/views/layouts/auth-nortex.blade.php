<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Entrar') — {{ config('app.name', 'NorteX') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/auth-nortex.css', 'resources/js/auth-nortex.js'])
</head>
<body class="nx-auth-body" data-app-name="{{ config('app.name', 'NorteX') }}">
    <div class="nx-auth-parallax-root" id="nx-auth-parallax" data-bg="{{ asset('images/nortex/parallax-sunset-yacht-jetski.jpg') }}">
        <div class="nx-auth-parallax-layer nx-auth-parallax-layer--back" data-depth="18" aria-hidden="true"></div>
        <div class="nx-auth-parallax-layer nx-auth-parallax-layer--front" data-depth="32" aria-hidden="true"></div>
        <div class="nx-auth-overlay" aria-hidden="true"></div>
        <div class="nx-auth-vignette" aria-hidden="true"></div>

        <main class="nx-auth-main">
            <div class="nx-auth-clock" id="nx-auth-clock" data-initial="{{ now()->timestamp * 1000 }}">
                <div class="nx-auth-clock__weekday" data-part="weekday"></div>
                <div class="nx-auth-clock__time" data-part="time"></div>
                <div class="nx-auth-clock__date" data-part="date"></div>
            </div>

            <div id="nx-auth-surface" class="nx-auth-surface nx-auth-surface--visible" data-nx-auth-surface>
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
