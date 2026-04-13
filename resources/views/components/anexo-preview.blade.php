@props([
    'url',
    'mime' => null,
    'nome' => '',
])

@php
    $mime = $mime ?? '';
    $isImage = str_starts_with($mime, 'image/');
    $lower = strtolower((string) $nome);
    $isPdf = $mime === 'application/pdf' || str_ends_with($lower, '.pdf');
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 overflow-hidden bg-gray-50']) }}>
    @if ($isImage)
        <img src="{{ $url }}" alt="" class="max-h-72 w-full object-contain bg-white" loading="lazy" />
    @elseif ($isPdf)
        <iframe src="{{ $url }}#toolbar=1" class="w-full h-80 bg-white" title="Pré-visualização PDF"></iframe>
    @else
        <p class="p-3 text-xs text-gray-600">Pré-visualização indisponível para este tipo. Use “Abrir em nova aba”.</p>
    @endif
</div>
