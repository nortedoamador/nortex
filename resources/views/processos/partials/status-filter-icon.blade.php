@php
    use App\Enums\ProcessoStatus;
    $nxStatusFilterIconClass = $class ?? 'h-5 w-5 shrink-0';
@endphp
{{-- Ícones alinhados à referência visual (traço 1.75, viewBox 24). --}}
<svg class="{{ $nxStatusFilterIconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
    @if ($status === null)
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
    @elseif ($status === ProcessoStatus::EmMontagem)
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
    @elseif ($status === ProcessoStatus::AProtocolar)
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.767 59.767 0 0 1 3.27 20.876 5.999 12Zm0 0h7.5" />
    @elseif ($status === ProcessoStatus::Protocolado)
        <circle cx="12" cy="5" r="3" fill="none" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v14M5 12H2a10 10 0 0 0 20 0h-3" />
    @elseif ($status === ProcessoStatus::EmAndamento)
        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 0 1 0 1.971l-11.54 6.347c-.75.412-1.667-.13-1.667-.986V5.653Z" />
    @elseif ($status === ProcessoStatus::EmExigencia)
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
    @elseif ($status === ProcessoStatus::AguardandoProva)
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 2.25h8v2.5l-3.5 7 3.5 7v2.5H8v-2.5l3.5-7L8 4.75v-2.5Z" />
    @elseif ($status === ProcessoStatus::Indeferido)
        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    @elseif ($status === ProcessoStatus::ADisposicao)
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 9V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2M4 9h16v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9Z" />
    @elseif ($status === ProcessoStatus::Concluido)
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    @endif
</svg>
