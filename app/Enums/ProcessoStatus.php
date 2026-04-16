<?php

namespace App\Enums;

use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;

/**
 * Fases do fluxo administrativo Marinha (NorteX).
 */
enum ProcessoStatus: string
{
    case EmMontagem = 'em_montagem';
    case AProtocolar = 'a_protocolar';
    case Protocolado = 'protocolado';
    case EmAndamento = 'em_andamento';
    case EmExigencia = 'em_exigencia';
    case AguardandoProva = 'aguardando_prova';
    case Indeferido = 'indeferido';
    case ADisposicao = 'a_disposicao';
    case Concluido = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::EmMontagem => 'Em montagem',
            self::AProtocolar => 'A protocolar',
            self::Protocolado => 'Protocolado',
            self::EmAndamento => 'Em andamento',
            self::EmExigencia => 'Em exigência',
            self::AguardandoProva => 'Aguardando prova',
            self::Indeferido => 'Indeferido',
            self::ADisposicao => 'À disposição',
            self::Concluido => 'Concluído',
        };
    }

    /**
     * Opções de etapa ao alterar um processo (exclui «Aguardando prova» quando o tipo de processo não prevê prova prática).
     *
     * @return list<self>
     */
    public static function opcoesParaAlteracao(TipoProcesso|PlatformTipoProcesso|null $tipo): array
    {
        if ($tipo?->permiteStatusAguardandoProva()) {
            return self::cases();
        }

        return array_values(array_filter(
            self::cases(),
            fn (self $s) => $s !== self::AguardandoProva,
        ));
    }

    public static function mensagemTipoNaoAceitaAguardandoProva(): string
    {
        return __('A etapa «Aguardando prova» só se aplica a processos de «Inscrição e emissão de Arrais-Amador» ou «Inscrição e emissão de Arrais-Amador Mestre-Amador».');
    }

    /** Ordem sugerida para colunas Kanban (esquerda → direita). */
    public static function kanbanOrder(): array
    {
        return [
            self::EmMontagem,
            self::AProtocolar,
            self::Protocolado,
            self::EmAndamento,
            self::EmExigencia,
            self::AguardandoProva,
            self::Indeferido,
            self::ADisposicao,
            self::Concluido,
        ];
    }

    /**
     * Chave da coluna no quadro resumido (lista/grade na página de processos).
     */
    public function gridResumoColumnKey(): string
    {
        return match ($this) {
            self::EmMontagem => 'em_montagem',
            self::AProtocolar => 'a_protocolar',
            self::Concluido => 'concluido',
            self::EmExigencia, self::AguardandoProva => 'pendente',
            default => 'outras',
        };
    }

    /** Metadados das colunas do quadro resumido (cor do indicador). */
    public static function gridResumoColumns(): array
    {
        return [
            ['key' => 'em_montagem', 'dot' => 'bg-[#F2C94C]'],
            ['key' => 'a_protocolar', 'dot' => 'bg-[#9B51E0]'],
            ['key' => 'concluido', 'dot' => 'bg-[#6FCF97]'],
            ['key' => 'pendente', 'dot' => 'bg-[#F2994A]'],
        ];
    }

    /** Cor institucional do status (referência visual única). */
    public function uiBrandHex(): string
    {
        return match ($this) {
            self::EmMontagem => '#F2C94C',
            self::AProtocolar => '#9B51E0',
            self::Protocolado => '#2F80ED',
            self::EmAndamento => '#56CCF2',
            self::EmExigencia => '#F2994A',
            self::AguardandoProva => '#BDBDBD',
            self::Indeferido => '#EB5757',
            self::ADisposicao => '#27AE60',
            self::Concluido => '#6FCF97',
        };
    }

    /**
     * Cor do ícone de etapa (traço) no select personalizado — alinhado à referência visual por fase.
     */
    public function uiStatusSelectIconClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'text-[#c9a227] dark:text-[#e8c547]',
            self::AProtocolar => 'text-[#7c3aed] dark:text-[#a78bfa]',
            self::Protocolado => 'text-[#2563eb] dark:text-[#60a5fa]',
            self::EmAndamento => 'text-[#0891b2] dark:text-[#22d3ee]',
            self::EmExigencia => 'text-[#ea580c] dark:text-[#fb923c]',
            self::AguardandoProva => 'text-[#737373] dark:text-[#a3a3a3]',
            self::Indeferido => 'text-[#dc2626] dark:text-[#f87171]',
            self::ADisposicao => 'text-[#16a34a] dark:text-[#4ede80]',
            self::Concluido => 'text-[#15803d] dark:text-[#4ade80]',
        };
    }

    /** Ponto colorido no cabeçalho da coluna Kanban (uma cor por fase). */
    public function uiKanbanColumnDotClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'bg-[#F2C94C]',
            self::AProtocolar => 'bg-[#9B51E0]',
            self::Protocolado => 'bg-[#2F80ED]',
            self::EmAndamento => 'bg-[#56CCF2]',
            self::EmExigencia => 'bg-[#F2994A]',
            self::AguardandoProva => 'bg-[#BDBDBD]',
            self::Indeferido => 'bg-[#EB5757]',
            self::ADisposicao => 'bg-[#27AE60]',
            self::Concluido => 'bg-[#6FCF97]',
        };
    }

    /**
     * Barra vertical à esquerda do cartão na lista de processos.
     */
    public function uiListAccentBarClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'bg-[#F2C94C]',
            self::AProtocolar => 'bg-[#9B51E0]',
            self::Protocolado => 'bg-[#2F80ED]',
            self::EmAndamento => 'bg-[#56CCF2]',
            self::EmExigencia => 'bg-[#F2994A]',
            self::AguardandoProva => 'bg-[#BDBDBD]',
            self::Indeferido => 'bg-[#EB5757]',
            self::ADisposicao => 'bg-[#27AE60]',
            self::Concluido => 'bg-[#6FCF97]',
        };
    }

    /**
     * Anel / fundo do ícone circular (lista + reutilização visual).
     */
    public function uiListStatusIconRingClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'bg-[#F2C94C]/25 text-zinc-800 dark:bg-[#F2C94C]/20 dark:text-zinc-100',
            self::AProtocolar => 'bg-[#9B51E0]/20 text-zinc-800 dark:bg-[#9B51E0]/25 dark:text-zinc-100',
            self::Protocolado => 'bg-[#2F80ED]/18 text-zinc-800 dark:bg-[#2F80ED]/25 dark:text-zinc-100',
            self::EmAndamento => 'bg-[#56CCF2]/25 text-zinc-800 dark:bg-[#56CCF2]/22 dark:text-zinc-100',
            self::EmExigencia => 'bg-[#F2994A]/22 text-zinc-800 dark:bg-[#F2994A]/25 dark:text-zinc-100',
            self::AguardandoProva => 'bg-[#BDBDBD]/40 text-zinc-800 dark:bg-[#BDBDBD]/45 dark:text-zinc-100',
            self::Indeferido => 'bg-[#EB5757]/18 text-zinc-800 dark:bg-[#EB5757]/25 dark:text-zinc-100',
            self::ADisposicao => 'bg-[#27AE60]/18 text-zinc-800 dark:bg-[#27AE60]/25 dark:text-zinc-100',
            self::Concluido => 'bg-[#6FCF97]/28 text-zinc-800 dark:bg-[#6FCF97]/30 dark:text-zinc-900',
        };
    }

    /**
     * Círculo sólido do avatar na lista de processos (cor = status; o símbolo vem do tipo de processo).
     */
    public function uiListStatusAvatarSolidClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'bg-[#F2C94C] shadow-md shadow-[#F2C94C]/30',
            self::AProtocolar => 'bg-[#9B51E0] shadow-md shadow-[#9B51E0]/30',
            self::Protocolado => 'bg-[#2F80ED] shadow-md shadow-[#2F80ED]/30',
            self::EmAndamento => 'bg-[#56CCF2] shadow-md shadow-[#56CCF2]/30',
            self::EmExigencia => 'bg-[#F2994A] shadow-md shadow-[#F2994A]/30',
            self::AguardandoProva => 'bg-[#BDBDBD] shadow-md shadow-[#BDBDBD]/35',
            self::Indeferido => 'bg-[#EB5757] shadow-md shadow-[#EB5757]/30',
            self::ADisposicao => 'bg-[#27AE60] shadow-md shadow-[#27AE60]/30',
            self::Concluido => 'bg-[#6FCF97] shadow-md shadow-[#6FCF97]/30',
        };
    }

    /**
     * Cor do ícone do avatar em fundos claros (amarelo, ciano, cinza, verde claro).
     */
    public function uiListStatusAvatarForegroundClass(): string
    {
        return match ($this) {
            self::EmMontagem,
            self::EmAndamento,
            self::AguardandoProva,
            self::Concluido => 'text-zinc-900',
            default => 'text-white',
        };
    }

    /**
     * Estilo inline para cada &lt;option&gt; do select nativo (lista aberta).
     * Fundo neutro e texto legível — evita o “arco-íris” por opção (melhor leitura e WCAG).
     */
    public function uiNativeSelectOptionStyle(): string
    {
        return 'background-color: #ffffff; color: #0f172a; font-weight: 500;';
    }

    /**
     * Contorno do controlo de etapa na lista: cartão neutro com barra lateral na cor institucional do status.
     */
    public function uiListSelectChromeWrapClass(): string
    {
        $accent = match ($this) {
            self::EmMontagem => 'border-l-[#F2C94C]',
            self::AProtocolar => 'border-l-[#9B51E0]',
            self::Protocolado => 'border-l-[#2F80ED]',
            self::EmAndamento => 'border-l-[#56CCF2]',
            self::EmExigencia => 'border-l-[#F2994A]',
            self::AguardandoProva => 'border-l-[#9e9e9e]',
            self::Indeferido => 'border-l-[#EB5757]',
            self::ADisposicao => 'border-l-[#27AE60]',
            self::Concluido => 'border-l-[#6FCF97]',
        };

        return 'relative inline-flex min-w-0 max-w-full items-center rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.04] transition-colors hover:border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:ring-white/[0.06] dark:hover:border-slate-500 border-l-[3px] '.$accent;
    }

    /** Interior do trigger / select: tipografia alinhada ao resto da app (sem fundo por status). */
    public function uiListSelectClasses(): string
    {
        return 'bg-transparent text-sm font-medium leading-snug text-slate-800 shadow-none focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-indigo-500/25 focus-visible:ring-inset dark:text-slate-100';
    }

    /** Cor da seta do dropdown (neutra; uma só seta na view). */
    public function uiListSelectChevronClass(): string
    {
        return 'text-slate-400 dark:text-slate-500';
    }

    /** Conteúdo interior da pílula só leitura (alinhado ao select da lista de processos). */
    public function uiListReadonlyPillClasses(): string
    {
        return 'block w-full min-w-0 whitespace-normal py-2.5 pl-3 pr-10 text-left '.$this->uiListSelectClasses();
    }

    /**
     * Etapas em que a ficha deve registar número e data de protocolo da Marinha.
     */
    public function exigeDadosProtocoloMarinha(): bool
    {
        return match ($this) {
            self::Protocolado,
            self::EmAndamento,
            self::EmExigencia,
            self::AguardandoProva,
            self::Indeferido,
            self::ADisposicao => true,
            default => false,
        };
    }
}
