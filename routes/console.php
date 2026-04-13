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

Artisan::command('nx:verify-cn-deps', function () {
    $this->info('Verificação de dependências para leitura de CNH (PDF / Imagick / QR / OCR)');
    $this->newLine();

    $ok = true;

    $this->line('PHP '.PHP_VERSION.' ('.(ZEND_THREAD_SAFE ? 'ZTS' : 'NTS').', '.(PHP_INT_SIZE === 8 ? 'x64' : 'x86').')');

    if (extension_loaded('imagick')) {
        $this->components->info('Imagick (extensão PHP): OK');
        try {
            $v = phpversion('imagick');
            $this->line('  Versão da extensão: '.($v ?: '?'));
        } catch (\Throwable) {
            // ignore
        }
    } else {
        $this->components->error('Imagick (extensão PHP): FALTA ou não carrega');
        $extDir = ini_get('extension_dir') ?: PHP_EXTENSION_DIR;
        $this->line('  Confirme: php_imagick.dll em '.$extDir);
        $this->line('  Coloque também as DLL do zip PECL (ou o bin do ImageMagick) no PATH / pasta do php.exe.');
        $ok = false;
    }

    $gs = trim((string) shell_exec('where gswin64c 2>nul'));
    if ($gs !== '' && str_contains($gs, 'gswin64c')) {
        $this->components->info('Ghostscript (PATH): OK');
        $ver = @shell_exec('gswin64c.exe -version 2>&1');
        if (is_string($ver) && $ver !== '') {
            $this->line('  '.trim(strtok($ver, "\n")));
        }
    } else {
        $this->components->warn('Ghostscript (PATH): não encontrado neste terminal');
        $this->line('  Instale o GS e adicione ...\\gs\\gs10.xx\\bin ao PATH; abra um terminal novo.');
    }

    $zbar = trim((string) config('cnh.zbarimg_path', ''));
    if ($zbar !== '' && (is_file($zbar) || (PHP_OS_FAMILY === 'Windows' && str_contains($zbar, '\\')))) {
        $this->components->info('zbarimg (CNH_ZBARIMG_PATH): configurado');
        $this->line('  '.$zbar);
    } else {
        $this->components->warn('zbarimg: CNH_ZBARIMG_PATH vazio ou ficheiro inexistente');
    }

    $tess = trim((string) config('cnh.tesseract_path', ''));
    if ($tess !== '') {
        $this->line('Tesseract: '.$tess.(is_file($tess) ? ' (ficheiro existe)' : ' (verifique o caminho)'));
    } else {
        $this->line('Tesseract: não configurado (OCR opcional)');
    }

    $this->newLine();
    $this->line($ok ? 'Resumo: pronto para rasterizar PDF com Imagick.' : 'Resumo: corrija Imagick antes de testar PDF da CNH.');

    return $ok ? 0 : 1;
})->purpose('Verifica Ghostscript, Imagick, zbarimg e Tesseract para extração de CNH');

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
