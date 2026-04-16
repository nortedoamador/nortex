<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\DocumentoTipo;
use App\Models\Embarcacao;
use App\Models\EmpresaCompromisso;
use App\Models\AulaNautica;
use App\Models\FinanceiroAdminDiretoLancamento;
use App\Models\FinanceiroAulaLancamento;
use App\Models\FinanceiroDespesaLancamento;
use App\Models\FinanceiroLoteEngenharia;
use App\Models\FinanceiroLoteEngenhariaItem;
use App\Models\FinanceiroLoteParceria;
use App\Models\FinanceiroLoteParceriaItem;
use App\Models\Habilitacao;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoPostIt;
use App\Models\Role;
use App\Models\TipoProcesso;
use App\Models\User;
use App\Observers\ModelActivityObserver;
use App\Support\TenantHashids;
use App\Policies\ClientePolicy;
use App\Policies\EmbarcacaoPolicy;
use App\Policies\HabilitacaoPolicy;
use App\Policies\ProcessoPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');

        $this->app->singleton(TenantHashids::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        Gate::policy(Processo::class, ProcessoPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(Embarcacao::class, EmbarcacaoPolicy::class);
        Gate::policy(Habilitacao::class, HabilitacaoPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        $auditObserver = app(ModelActivityObserver::class);
        Cliente::observe($auditObserver);
        Embarcacao::observe($auditObserver);
        Habilitacao::observe($auditObserver);
        Processo::observe($auditObserver);
        ProcessoDocumento::observe($auditObserver);
        ProcessoPostIt::observe($auditObserver);
        TipoProcesso::observe($auditObserver);
        DocumentoTipo::observe($auditObserver);
        DocumentoModelo::observe($auditObserver);
        EmpresaCompromisso::observe($auditObserver);
        AulaNautica::observe($auditObserver);
        FinanceiroAulaLancamento::observe($auditObserver);
        FinanceiroAdminDiretoLancamento::observe($auditObserver);
        FinanceiroDespesaLancamento::observe($auditObserver);
        FinanceiroLoteEngenharia::observe($auditObserver);
        FinanceiroLoteEngenhariaItem::observe($auditObserver);
        FinanceiroLoteParceria::observe($auditObserver);
        FinanceiroLoteParceriaItem::observe($auditObserver);

        View::composer('layouts.sidebar', function (\Illuminate\View\View $view): void {
            $u = Auth::user();
            $tenantPlanoAtivo = true;
            if ($u && $u->empresa_id) {
                $u->loadMissing('empresa');
                $tenantPlanoAtivo = (bool) ($u->empresa && $u->empresa->assinaturaPlataformaAtiva());
            }
            $view->with('tenantPlanoAtivo', $tenantPlanoAtivo);
        });
    }
}
