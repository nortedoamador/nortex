<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\DocumentoTipo;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\Role;
use App\Models\TipoProcesso;
use App\Models\User;
use App\Observers\ModelActivityObserver;
use App\Policies\ClientePolicy;
use App\Policies\EmbarcacaoPolicy;
use App\Policies\HabilitacaoPolicy;
use App\Policies\ProcessoPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
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
        TipoProcesso::observe($auditObserver);
        DocumentoTipo::observe($auditObserver);
        DocumentoModelo::observe($auditObserver);
    }
}
