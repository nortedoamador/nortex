<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Impede que {@see RefreshDatabase} rode migrate:fresh em MySQL/MariaDB/PostgreSQL/SQL Server.
 *
 * O Laravel ainda usa migrate:fresh em SQLite (incl. :memory: do phpunit.xml), o que não apaga seu MySQL do XAMPP.
 * Se os testes falharem com exceção aqui, rode: php artisan config:clear (evita config em cache apontando para mysql).
 */
trait SafeRefreshDatabase
{
    use RefreshDatabase {
        migrateDatabases as protected runLaravelMigrateFresh;
    }

    /** @var list<string> */
    private const DRIVERS_QUE_NUNCA_RECEBEM_MIGRATE_FRESH_NOS_TESTES = [
        'mysql',
        'mariadb',
        'pgsql',
        'sqlsrv',
    ];

    protected function migrateDatabases(): void
    {
        foreach ($this->connectionsToTransact() as $name) {
            $driver = (string) config("database.connections.{$name}.driver");

            if (in_array($driver, self::DRIVERS_QUE_NUNCA_RECEBEM_MIGRATE_FRESH_NOS_TESTES, true)) {
                throw new \RuntimeException(
                    'migrate:fresh bloqueado na conexão "'.$name.'" (driver '.$driver.') para não apagar o banco de desenvolvimento. '.
                    'Use SQLite nos testes (phpunit.xml: DB_CONNECTION=sqlite, DB_DATABASE=:memory:). '.
                    'Se já está assim, execute: php artisan config:clear'
                );
            }
        }

        $this->runLaravelMigrateFresh();
    }
}
