{{-- nx_anexo_5d_var_preamble — Variáveis NORMAM (ANEXO 5-D). Injetado no upload ou incluído nos defaults. --}}
@php
    $cha_dt_emissao_fmt = $cha_dt_emissao_fmt ?? '';
    $cha_emissao_dia = $cha_emissao_dia ?? '';
    $cha_emissao_mes = $cha_emissao_mes ?? '';
    $cha_emissao_ano = $cha_emissao_ano ?? '';
    $rg_dia = $rg_dia ?? '';
    $rg_mes = $rg_mes ?? '';
    $rg_ano = $rg_ano ?? '';
    $local_declaracao = $local_declaracao ?? '';

    $rg = filled($rg ?? null) ? $rg : ($cliente->rg ?? $cliente->documento_identidade_numero ?? '');

    if ($rg_dia === '' && $rg_mes === '' && $rg_ano === '' && filled($dt_emissao_fmt ?? null)
        && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim((string) $dt_emissao_fmt), $nx5d_m)) {
        $rg_dia = $nx5d_m[1];
        $rg_mes = $nx5d_m[2];
        $rg_ano = $nx5d_m[3];
    }

    if (($cha_emissao_dia === '' || $cha_emissao_mes === '' || $cha_emissao_ano === '') && filled($cha_dt_emissao_fmt)
        && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim((string) $cha_dt_emissao_fmt), $nx5d_c)) {
        $cha_emissao_dia = filled($cha_emissao_dia) ? $cha_emissao_dia : $nx5d_c[1];
        $cha_emissao_mes = filled($cha_emissao_mes) ? $cha_emissao_mes : $nx5d_c[2];
        $cha_emissao_ano = filled($cha_emissao_ano) ? $cha_emissao_ano : $nx5d_c[3];
    }

    $decl_dia = '';
    $decl_mes = '';
    $decl_ano = '';
    if (isset($hoje) && $hoje instanceof \DateTimeInterface) {
        $decl_dia = $hoje->format('d');
        $decl_mes = $hoje->format('m');
        $decl_ano = $hoje->format('Y');
    }

    if ($local_declaracao === '') {
        $local_declaracao = $cidade ?? '';
    }

    $numero_compl = trim(trim((string) ($numero ?? '')).' '.trim((string) ($complemento ?? '')));
    $cidade_uf_linha = trim(($cidade ?? '').(($uf ?? '') !== '' ? ' / '.$uf : ''));
@endphp
