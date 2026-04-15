<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Models\Empresa;
use App\Services\EmpresaProcessosDefaultsService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nx:ensure-sessions', function () {
    if (Schema::hasTable('sessions')) {
        $this->components->info('Tabela sessions já existe.');

        return 0;
    }

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });

    $this->components->info('Tabela sessions criada com sucesso.');

    return 0;
})->purpose('Cria a tabela sessions quando SESSION_DRIVER=database');

Artisan::command('nx:processos:warmup {--empresa_id=} {--force}', function () {
    $empresaId = $this->option('empresa_id');
    $force = (bool) $this->option('force');

    $q = Empresa::query()->orderBy('id');
    if (filled($empresaId)) {
        $q->whereKey((int) $empresaId);
    }

    $svc = app(EmpresaProcessosDefaultsService::class);

    $n = 0;
    foreach ($q->cursor() as $empresa) {
        $this->line('Empresa #'.$empresa->id.' — '.$empresa->nome);
        if ($force) {
            // Para forçar nova sincronização, incremente TEMPLATE_BASICO_CACHE_BUSTER no serviço ou limpe o cache.
            $this->components->warn('Force: rodando mesmo com cache setado.');
        }
        $svc->garantirTemplateBasico($empresa);
        $n++;
    }

    $this->components->info('Warmup concluído para '.$n.' empresa(s).');

    return 0;
})->purpose('Pré-aquece templates/checklists de processos por empresa');
