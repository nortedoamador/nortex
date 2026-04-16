@php
    $name = request()->route()?->getName() ?? '';
    $perfilAtivo = $name === 'aulas.escola.edit';
    $instrutoresAtivo = $name === 'aulas.escola.instrutores';
    $visaoGeralAtiva = $name === 'aulas.index'
        || $name === 'aulas.show'
        || $name === 'aulas.create'
        || $name === 'aulas.edit'
        || ($name !== '' && str_starts_with($name, 'aulas.pdf.'));
@endphp
<nav class="mb-6 flex flex-wrap gap-2" aria-label="{{ __('Escola Náutica') }}">
    <x-escola-nav-pill :href="route('aulas.index')" :active="$visaoGeralAtiva">{{ __('Visão geral') }}</x-escola-nav-pill>
    @if (auth()->user()?->hasPermission('aulas.manage'))
        <x-escola-nav-pill :href="route('aulas.escola.edit')" :active="$perfilAtivo">{{ __('Perfil da escola') }}</x-escola-nav-pill>
        <x-escola-nav-pill :href="route('aulas.escola.instrutores')" :active="$instrutoresAtivo">{{ __('Instrutores') }}</x-escola-nav-pill>
    @endif
    <x-escola-nav-pill :href="route('aulas.atestados.index')" :active="str_starts_with($name, 'aulas.atestados')">{{ __('Atestados') }}</x-escola-nav-pill>
    <x-escola-nav-pill :href="route('aulas.comunicados.index')" :active="str_starts_with($name, 'aulas.comunicados')">{{ __('Comunicados') }}</x-escola-nav-pill>
</nav>
