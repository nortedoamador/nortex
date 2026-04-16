<x-app-layout :title="__('Tour — NorteX')">
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Tour do sistema') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Visão geral para começar a trabalhar com confiança no NorteX.') }}</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
        <nav class="sticky top-0 z-10 -mx-4 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 sm:-mx-6 sm:px-6">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Nesta página') }}</p>
            <ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-indigo-600 dark:text-indigo-400">
                <li><a href="#visao-geral" class="hover:underline">{{ __('Visão geral') }}</a></li>
                <li><a href="#dashboard-processos" class="hover:underline">{{ __('Dashboard e processos') }}</a></li>
                <li><a href="#checklist" class="hover:underline">{{ __('Checklist e anexos') }}</a></li>
                <li><a href="#cadastros" class="hover:underline">{{ __('Clientes e cadastros') }}</a></li>
                <li><a href="#plano-financeiro" class="hover:underline">{{ __('Plano e financeiro') }}</a></li>
                <li><a href="#equipe-admin" class="hover:underline">{{ __('Equipe e administração') }}</a></li>
                <li><a href="#boas-praticas" class="hover:underline">{{ __('Boas práticas') }}</a></li>
            </ul>
        </nav>

        <section id="visao-geral" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('O que é o NorteX?') }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                {{ __('O NorteX reúne a gestão de clientes, embarcações, habilitações e processos administrativos num único painel. O menu à esquerda é o ponto de partida: use-o para saltar entre módulos. O item «Tour» (esta página) fica sempre disponível para rever estes passos.') }}
            </p>
        </section>

        <section id="dashboard-processos" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Dashboard e processos') }}</h3>
            <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                <li>{{ __('No :dash abre-se o resumo do dia e atalhos para o que precisa de atenção.', ['dash' => __('Dashboard')]) }}</li>
                <li>{{ __('Em «Processos» cria um novo fluxo escolhendo cliente, embarcação (se aplicável) e tipo de processo. O sistema gera o checklist conforme as regras desse tipo.') }}</li>
                <li>{{ __('A vista em lista e o quadro Kanban mostram os mesmos processos; no Kanban pode arrastar cartões entre colunas para alterar o estado, quando tiver permissão e o fluxo permitir.') }}</li>
                <li>{{ __('Abra sempre a ficha do processo (:url) para ver detalhes, notas e checklist completo.', ['url' => __('Processos → linha → abrir')]) }}</li>
            </ol>
        </section>

        <section id="checklist" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Checklist, modelos PDF e anexos') }}</h3>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                <li>{{ __('Cada linha do checklist corresponde a um documento exigido. Ícones permitem: gerar modelo PDF, declarar entrega por modelo, anexar ficheiro ou marcar entrega em papel («Físico»).') }}</li>
                <li>{{ __('Itens como a procuração podem ficar satisfeitos automaticamente pelo modelo PDF da empresa — não precisa de anexar ficheiro se o modelo cobrir o caso.') }}</li>
                <li>{{ __('Para anexos, respeite o limite indicado na ficha do processo (:max). Imagens são comprimidas no servidor quando possível.', ['max' => upload_max_file_help()]) }}</li>
                <li>{{ __('Se alterar dados na ficha do cliente ou da embarcação, volte ao processo: o sistema pode sincronizar linhas do checklist com a informação já guardada.') }}</li>
            </ul>
        </section>

        <section id="cadastros" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Clientes, embarcações e habilitações') }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                {{ __('Mantenha cadastros completos antes de abrir processos: CNH, comprovantes e fotos da embarcação alimentam o checklist e reduzem retrabalho. Nos formulários de ficha, os anexos seguem o mesmo limite global (:max).', ['max' => upload_max_file_help()]) }}
            </p>
        </section>

        <section id="plano-financeiro" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Plano de assinatura e módulo financeiro') }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                {{ __('O menu «Plano» mostra o estado da subscrição Stripe da empresa. O plano Essencial não inclui o módulo financeiro: o item aparece desactivado e as rotas são bloqueadas. O plano Completo desbloqueia o financeiro para utilizadores com a permissão adequada.') }}
            </p>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                {{ __('Administradores da plataforma podem ajustar o plano na edição da empresa (Stripe), sujeito à configuração dos preços no ambiente.') }}
            </p>
        </section>

        <section id="equipe-admin" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Equipe, papéis e administração') }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                {{ __('Em «Equipe» quem tiver permissão convida utilizadores e define papéis (permissões). Em «Administração» na empresa ajustam-se tipos de processo, documentos do checklist, modelos PDF e outras opções — conforme o seu papel.') }}
            </p>
        </section>

        <section id="boas-praticas" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Boas práticas') }}</h3>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                <li>{{ __('Guarde processos com observações claras nos post-its da ficha — a equipa vê o contexto ao retomar o trabalho.') }}</li>
                <li>{{ __('Antes de mudar estado no Kanban, confirme pendências documentais quando o sistema pedir — evita processos «verdes» sem documentação.') }}</li>
                <li>{{ __('Use anexos legíveis e dentro do limite; PDF e imagem costumam ser mais simples de validar do que documentos Word muito pesados.') }}</li>
            </ul>
        </section>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Voltar ao dashboard') }}</a>
        </p>
    </div>
</x-app-layout>
