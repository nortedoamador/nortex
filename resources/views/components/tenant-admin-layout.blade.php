@props(['title' => null])

@php
$usePlatform = \App\Support\TenantEmpresaContext::isPlatformEmpresaAdminRoute();
@endphp

@if ($usePlatform)
<x-platform-layout :title="$title">
    @isset($header)
    <x-slot name="header">
        @include('platform.empresas.partials.admin-subnav')
        <div class="mt-4">{{ $header }}</div>
    </x-slot>
    @else
    <x-slot name="header">
        @include('platform.empresas.partials.admin-subnav')
    </x-slot>
    @endisset
    {{ $slot }}
</x-platform-layout>
@else
<x-app-layout :title="$title">
    @isset($header)
    <x-slot name="header">{{ $header }}</x-slot>
    @endisset
    {{ $slot }}
</x-app-layout>
@endif
