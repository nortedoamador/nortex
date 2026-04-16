@props([])
{{-- data-turbo="true" reactiva navegação Turbo dentro do hub quando o body tem data-turbo="false" (resto do site em navegação completa). --}}
<div class="px-4 py-6 sm:px-6 lg:px-8" data-turbo="true">
    <turbo-frame id="nx-escola-hub" class="block" data-turbo-action="advance">
        @include('aulas.partials.subnav')
        {{ $slot }}
    </turbo-frame>
</div>
