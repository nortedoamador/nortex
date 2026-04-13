# Banco de dados — práticas no desenvolvimento (NorteX)

Regras para não perder dados no MySQL (ou outro SGBD) usado no `.env` local ou em ambientes com dados importantes.

## Comandos a evitar no banco com dados reais

- Não rodar **`migrate:fresh`**, **`db:wipe`** nem **`migrate:refresh`** no banco onde estão dados importantes. Eles removem ou recriam estrutura e apagam o conteúdo das tabelas.
- Preferir apenas **`php artisan migrate`** para aplicar migrations novas.

## Testes automatizados

- Os testes do Laravel usam internamente **`migrate:fresh`** na conexão de teste. Rode **`composer test`**, que executa **`config:clear`** antes e reduz o risco de a configuração em cache apontar para o MySQL do `.env`.
- Se usar **`php artisan test`** diretamente, execute antes **`php artisan config:clear`** (ou confirme que não há `bootstrap/cache/config.php` desatualizado).
- O projeto usa o trait **`Tests\Concerns\SafeRefreshDatabase`**, que **bloqueia** `migrate:fresh` em drivers **mysql, mariadb, pgsql e sqlsrv** durante os testes; em **SQLite** (como no `phpunit.xml`, `:memory:`) o refresh continua a ser usado só nessa base de teste.

## Backup

- Manter **backup periódico** do MySQL (export `.sql` semanal ou antes de migrations arriscadas). Sem backup não há como recuperar dados após um `fresh` ou erro manual.

## Ver também

- [Política de backups](./BACKUPS.md)
