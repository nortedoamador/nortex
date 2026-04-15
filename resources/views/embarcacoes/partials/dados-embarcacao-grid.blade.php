{{-- Grelha tipo ficha técnica: rótulo + ícone por célula; $embarcacao --}}
@php
    $e = $embarcacao;
    $tx = fn ($v) => filled(trim((string) ($v ?? ''))) ? trim((string) $v) : null;
    $d = fn ($v) => $tx($v) ?? '—';

    $porto = collect([$e->porto_cidade, $e->porto_uf])->filter()->implode(' / ');

    $comp = $tx($e->comprimento_m) ?? $tx($e->comprimento);
    $compStr = $comp !== null ? (is_numeric($comp) ? str_replace('.', ',', (string) $comp).' m' : $comp) : null;

    $motoresExibir = $e->motoresParaExibicao();
    $motorValueHtml = null;
    if (count($motoresExibir) > 0) {
        $linhas = [];
        foreach ($motoresExibir as $idx => $mot) {
            $bits = [];
            $marca = $tx($mot['marca'] ?? '');
            if ($marca !== null) {
                $bits[] = e($marca);
            }
            $pot = $tx($mot['potencia'] ?? '');
            if ($pot !== null) {
                $bits[] = e($pot);
            }
            $num = $tx($mot['numero_serie'] ?? '');
            if ($num !== null) {
                $bits[] = e('# '.$num);
            }
            if (count($bits) === 0) {
                continue;
            }
            $sep = ' <span class="text-slate-400 dark:text-slate-500" aria-hidden="true">·</span> ';
            $linhas[] = '<p class="leading-snug"><span class="text-slate-500 dark:text-slate-400">'
                .e(__('Motor')).' '.($idx + 1).':</span> '.implode($sep, $bits).'</p>';
        }
        if (count($linhas) > 0) {
            $motorValueHtml = '<div class="space-y-1.5">'.implode('', $linhas).'</div>';
        }
    }

    $pass = $tx($e->passageiros);
    $lotaStr = $pass !== null ? (is_numeric($pass) ? $pass.' '.__('pessoas') : $pass) : null;

    $alienacaoTxt = match ((string) ($e->alienacao_fiduciaria ?? '')) {
        'sim' => __('Sim'),
        'nao' => __('Não'),
        default => null,
    };

    $cells = [
        ['label' => __('Nº inscrição'), 'value' => $d($e->inscricao), 'icon' => 'hash'],
        ['label' => __('Porto / base'), 'value' => $d($porto), 'icon' => 'map'],
        ['label' => __('Data emissão (inscrição)'), 'value' => $e->inscricao_data_emissao?->format('d/m/Y') ?? '—', 'icon' => 'calendar'],
        ['label' => __('Data vencimento'), 'value' => $e->inscricao_data_vencimento?->format('d/m/Y') ?? '—', 'icon' => 'calendar'],
        ['label' => __('Jurisdição (inscrição)'), 'value' => $d($e->inscricao_jurisdicao), 'icon' => 'map'],
        ['label' => __('Alienação fiduciária'), 'value' => $alienacaoTxt ?? '—', 'icon' => 'flag'],
        ['label' => __('Credor hipotecário'), 'value' => $e->alienacao_fiduciaria === 'sim' ? $d($e->credor_hipotecario) : '—', 'icon' => 'building'],
        ['label' => __('Tipo'), 'value' => $d($e->tipo), 'icon' => 'boat'],
        ['label' => __('Atividade'), 'value' => $d($e->atividade), 'icon' => 'flag'],
        ['label' => __('Tipo de navegação'), 'value' => $e->tipo_navegacao?->label() ?? '—', 'icon' => 'boat'],
        ['label' => __('Área de navegação'), 'value' => $e->area_navegacao?->label() ?? '—', 'icon' => 'map'],
        ['label' => __('Lotação'), 'value' => $d($lotaStr), 'icon' => 'users'],
        ['label' => __('Tripulantes'), 'value' => $d($e->tripulantes), 'icon' => 'users'],
        ['label' => __('Arqueação bruta'), 'value' => $d($e->arqueacao_bruta), 'icon' => 'scale'],
        ['label' => __('Arqueação líquida'), 'value' => $d($e->arqueacao_liquida), 'icon' => 'scale'],
        ['label' => __('Comprimento'), 'value' => $d($compStr), 'icon' => 'ruler'],
        ['label' => __('Material do casco'), 'value' => $d($e->material_casco), 'icon' => 'cube'],
        ['label' => __('Potência máx. do casco'), 'value' => $d($e->potencia_maxima_casco), 'icon' => 'bolt'],
        ['label' => __('Cor do casco'), 'value' => $d($e->cor_casco_ficha ?? $e->cor_casco), 'icon' => 'swatch'],
        ['label' => __('Propulsão'), 'value' => $e->tipo_propulsao?->label() ?? '—', 'icon' => 'cog'],
        ['label' => __('Motores'), 'value' => $motorValueHtml !== null ? $motorValueHtml : '—', 'icon' => 'bolt', 'html' => $motorValueHtml !== null],
        ['label' => __('Construtor'), 'value' => $d($e->construtor), 'icon' => 'building'],
        ['label' => __('Ano construção'), 'value' => $d($e->ano_construcao), 'icon' => 'calendar'],
    ];
@endphp

<section id="dados" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-600 to-indigo-600 text-white shadow-md ring-2 ring-violet-500/25 dark:from-violet-500 dark:to-indigo-500 dark:ring-indigo-400/30" aria-hidden="true">
                @include('embarcacoes.partials.icon-tipo-embarcacao', ['tipo' => $e->tipo, 'svgClass' => 'h-6 w-6'])
            </span>
            <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-white sm:text-lg">
                {{ __('Dados da embarcação') }}
            </h2>
        </div>
        @can('update', $e)
            <a href="{{ route('embarcacoes.edit', $e) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                </svg>
                {{ __('Editar') }}
            </a>
        @endcan
    </div>

    <div
        class="grid grid-cols-1 border-t border-slate-200 bg-slate-50/40 dark:border-slate-800 dark:bg-slate-950/20 sm:grid-cols-2 xl:grid-cols-4 [&>div]:border-b [&>div]:border-r [&>div]:border-slate-200/90 dark:[&>div]:border-slate-800 max-sm:[&>div]:border-r-0 max-xl:sm:[&>div:nth-child(2n)]:border-r-0 xl:[&>div:nth-child(4n)]:border-r-0"
        role="list"
        aria-label="{{ __('Dados técnicos da embarcação') }}"
    >
        @foreach ($cells as $c)
            @include('embarcacoes.partials.dados-embarcacao-celula', [
                'label' => $c['label'],
                'icon' => $c['icon'],
                'value' => $c['value'],
                'valueIsHtml' => $c['html'] ?? false,
            ])
        @endforeach
    </div>
</section>
