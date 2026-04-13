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
     * Opções de etapa ao alterar um processo (exclui «Aguardando prova» quando o tipo de serviço não prevê prova prática).
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
     * Estilo inline para cada &lt;option&gt; do select nativo (lista aberta: Chrome, Edge, Firefox).
     * Usa uiBrandHex() e o mesmo contraste de texto que o avatar da lista.
     */
    public function uiNativeSelectOptionStyle(): string
    {
        $fg = match ($this) {
            self::EmMontagem,
            self::EmAndamento,
            self::AguardandoProva,
            self::Concluido => '#18181b',
            default => '#ffffff',
        };

        return 'background-color: '.$this->uiBrandHex().'; color: '.$fg.'; font-weight: 600;';
    }

    /**
     * Anel 1px na cor do status (padding do wrapper). O contorno não depende da borda do &lt;select&gt; (evita @tailwindcss/forms).
     */
    public function uiListSelectChromeWrapClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'inline-flex max-w-full rounded-full bg-[#F2C94C] p-px shadow-sm transition-all hover:brightness-95',
            self::AProtocolar => 'inline-flex max-w-full rounded-full bg-[#9B51E0] p-px shadow-sm transition-all hover:brightness-95',
            self::Protocolado => 'inline-flex max-w-full rounded-full bg-[#2F80ED] p-px shadow-sm transition-all hover:brightness-95',
            self::EmAndamento => 'inline-flex max-w-full rounded-full bg-[#56CCF2] p-px shadow-sm transition-all hover:brightness-95',
            self::EmExigencia => 'inline-flex max-w-full rounded-full bg-[#F2994A] p-px shadow-sm transition-all hover:brightness-95',
            self::AguardandoProva => 'inline-flex max-w-full rounded-full bg-[#BDBDBD] p-px shadow-sm transition-all hover:brightness-95',
            self::Indeferido => 'inline-flex max-w-full rounded-full bg-[#EB5757] p-px shadow-sm transition-all hover:brightness-95',
            self::ADisposicao => 'inline-flex max-w-full rounded-full bg-[#27AE60] p-px shadow-sm transition-all hover:brightness-95',
            self::Concluido => 'inline-flex max-w-full rounded-full bg-[#6FCF97] p-px shadow-sm transition-all hover:brightness-95',
        };
    }

    /** Interior: sem borda (anel = wrap). Classes extras no &lt;select&gt; vêm da view + CSS nx-processo-list-status. */
    public function uiListSelectClasses(): string
    {
        return match ($this) {
            self::EmMontagem => 'rounded-full bg-[#F2C94C]/14 text-zinc-900 shadow-none hover:bg-[#F2C94C]/22 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#F2C94C]/40 focus-visible:ring-inset dark:bg-[#F2C94C]/16 dark:text-zinc-50 dark:hover:bg-[#F2C94C]/24',
            self::AProtocolar => 'rounded-full bg-[#9B51E0]/12 text-zinc-900 shadow-none hover:bg-[#9B51E0]/20 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#9B51E0]/45 focus-visible:ring-inset dark:bg-[#9B51E0]/18 dark:text-zinc-50 dark:hover:bg-[#9B51E0]/26',
            self::Protocolado => 'rounded-full bg-[#2F80ED]/12 text-zinc-900 shadow-none hover:bg-[#2F80ED]/20 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#2F80ED]/45 focus-visible:ring-inset dark:bg-[#2F80ED]/18 dark:text-zinc-50 dark:hover:bg-[#2F80ED]/26',
            self::EmAndamento => 'rounded-full bg-[#56CCF2]/18 text-zinc-900 shadow-none hover:bg-[#56CCF2]/28 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#56CCF2]/45 focus-visible:ring-inset dark:bg-[#56CCF2]/20 dark:text-zinc-50 dark:hover:bg-[#56CCF2]/30',
            self::EmExigencia => 'rounded-full bg-[#F2994A]/14 text-zinc-900 shadow-none hover:bg-[#F2994A]/22 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#F2994A]/45 focus-visible:ring-inset dark:bg-[#F2994A]/18 dark:text-zinc-50 dark:hover:bg-[#F2994A]/26',
            self::AguardandoProva => 'rounded-full bg-[#BDBDBD]/28 text-zinc-900 shadow-none hover:bg-[#BDBDBD]/40 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#9e9e9e]/50 focus-visible:ring-inset dark:bg-[#BDBDBD]/32 dark:text-zinc-100 dark:hover:bg-[#BDBDBD]/45',
            self::Indeferido => 'rounded-full bg-[#EB5757]/12 text-zinc-900 shadow-none hover:bg-[#EB5757]/20 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#EB5757]/45 focus-visible:ring-inset dark:bg-[#EB5757]/18 dark:text-zinc-50 dark:hover:bg-[#EB5757]/26',
            self::ADisposicao => 'rounded-full bg-[#27AE60]/12 text-zinc-900 shadow-none hover:bg-[#27AE60]/20 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#27AE60]/45 focus-visible:ring-inset dark:bg-[#27AE60]/18 dark:text-zinc-50 dark:hover:bg-[#27AE60]/26',
            self::Concluido => 'rounded-full bg-[#6FCF97]/18 text-zinc-900 shadow-none hover:bg-[#6FCF97]/28 focus:outline-none focus:ring-0 focus-visible:ring-2 focus-visible:ring-[#6FCF97]/45 focus-visible:ring-inset dark:bg-[#6FCF97]/22 dark:text-zinc-50 dark:hover:bg-[#6FCF97]/32',
        };
    }

    /** Cor da seta (mesma família do anel / texto). */
    public function uiListSelectChevronClass(): string
    {
        return match ($this) {
            self::EmMontagem => 'text-[#a67f0a] dark:text-[#f5e6a8]',
            self::AProtocolar => 'text-[#6b2d9e] dark:text-[#d4b8f0]',
            self::Protocolado => 'text-[#1a5cb8] dark:text-[#a8c8f8]',
            self::EmAndamento => 'text-[#1a7a9e] dark:text-[#b8e8fa]',
            self::EmExigencia => 'text-[#b86b1a] dark:text-[#fad4a8]',
            self::AguardandoProva => 'text-[#616161] dark:text-[#e0e0e0]',
            self::Indeferido => 'text-[#c0392b] dark:text-[#f5b8b8]',
            self::ADisposicao => 'text-[#1e8449] dark:text-[#a8e6c4]',
            self::Concluido => 'text-[#2d8f56] dark:text-[#0d3d22]',
        };
    }

    /** Conteúdo interior da pílula só leitura (alinhado ao select da lista de processos). */
    public function uiListReadonlyPillClasses(): string
    {
        return 'block w-full min-w-0 whitespace-normal px-4 py-3 text-left text-sm font-semibold leading-snug '.$this->uiListSelectClasses();
    }
}
