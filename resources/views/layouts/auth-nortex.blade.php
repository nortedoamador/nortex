@php
    // #region agent log
    try {
        $mf = public_path('build/manifest.json');
        $assetsDir = public_path('build/assets');
        $payload = [
            'sessionId' => 'bc9c3d',
            'hypothesisId' => 'H1',
            'location' => 'layouts/auth-nortex.blade.php',
            'message' => 'server_build_probe',
            'data' => [
                'manifest_exists' => is_file($mf),
                'manifest_bytes' => is_file($mf) ? filesize($mf) : 0,
                'assets_dir_exists' => is_dir($assetsDir),
                'assets_file_count' => is_dir($assetsDir) ? count(glob($assetsDir.'/*')) : 0,
                'app_url' => (string) config('app.url'),
                'request_path' => request()->path(),
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
            'runId' => 'pre-fix',
        ];
        @file_put_contents(base_path('debug-bc9c3d.log'), json_encode($payload)."\n", FILE_APPEND | LOCK_EX);
    } catch (\Throwable $e) {
        @file_put_contents(base_path('debug-bc9c3d.log'), json_encode([
            'sessionId' => 'bc9c3d',
            'hypothesisId' => 'H1',
            'message' => 'server_build_probe_error',
            'data' => ['err' => $e->getMessage()],
            'timestamp' => (int) round(microtime(true) * 1000),
            'runId' => 'pre-fix',
        ])."\n", FILE_APPEND | LOCK_EX);
    }
    // #endregion
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Entrar') — {{ config('app.name', 'NorteX') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
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
    <script>
    // #region agent log
    (function () {
        function send(msg, hid, data) {
            fetch('http://127.0.0.1:7902/ingest/d5e0dfbe-4898-4e54-8349-aaf5bfe73113', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': 'bc9c3d' },
                body: JSON.stringify({
                    sessionId: 'bc9c3d',
                    hypothesisId: hid,
                    location: 'layouts/auth-nortex.blade.php:inline',
                    message: msg,
                    data: data,
                    timestamp: Date.now(),
                    runId: 'pre-fix',
                }),
            }).catch(function () {});
        }
        var scripts = Array.from(document.scripts || []).map(function (s) { return s.src || ''; }).filter(Boolean);
        send('dom_script_srcs', 'H4', { scripts: scripts, buildScripts: scripts.filter(function (u) { return u.indexOf('/build/') !== -1; }) });
        window.addEventListener('load', function () {
            var r = (performance.getEntriesByType && performance.getEntriesByType('resource')) || [];
            var build = r.filter(function (e) { return e.name.indexOf('/build/') !== -1; }).map(function (e) {
                return { name: e.name, responseStatus: e.responseStatus, transferSize: e.transferSize };
            });
            send('load_resource_timing', 'H2', { href: location.href, build: build });
        });
    })();
    // #endregion
    </script>
</body>
</html>
