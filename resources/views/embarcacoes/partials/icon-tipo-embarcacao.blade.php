@php
    /** @var string|null $tipo */
    /** @var string $svgClass classes Tailwind do SVG (ex.: h-12 w-12 ou h-7 w-7) */
    $tipoNorm = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) ($tipo ?? '')));
    $isMotoAquatica = str_contains($tipoNorm, 'moto-aquatica');
    $svgClass = $svgClass ?? 'h-12 w-12';
@endphp
@if ($isMotoAquatica)
    @php
        $nxJetSkiPath = public_path('svg/jet-ski-svgrepo.svg');
        $nxJetSkiMarkup = is_readable($nxJetSkiPath) ? file_get_contents($nxJetSkiPath) : '';
        if ($nxJetSkiMarkup !== '') {
            $nxJetSkiMarkup = preg_replace('/<\?xml[^>]*>\s*/i', '', $nxJetSkiMarkup);
            $nxJetSkiMarkup = preg_replace('/<!--.*?-->\s*/s', '', $nxJetSkiMarkup, 1);
            $nxJetSkiClass = e($svgClass).' shrink-0';
            $nxJetSkiMarkup = preg_replace(
                '/<svg\s/',
                '<svg class="'.$nxJetSkiClass.'" aria-hidden="true" focusable="false" ',
                $nxJetSkiMarkup,
                1
            );
        }
    @endphp
    @if ($nxJetSkiMarkup !== '')
        {!! $nxJetSkiMarkup !!}
    @else
        <svg class="{{ $svgClass }} shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M10 5h1.4a1 1 0 0 1 .882.53L14 8.75" />
            <path d="m3.485 16.94l.136.545A2 2 0 0 0 5.561 19H13a10 10 0 0 0 8-4c0-6-5-8-5-8c-1.889 2.518-5.852 4-9 4H5a2 2 0 0 0-2 2c0 1.328.163 2.652.485 3.94M3.25 15H21" />
        </svg>
    @endif
@else
    <svg class="{{ $svgClass }} shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 18l-1 -5h18l-2 4" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13v-6h8l4 6" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7v-4h-1" />
    </svg>
@endif
