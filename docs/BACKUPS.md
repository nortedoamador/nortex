# Política de backups (NorteX)

Este documento descreve o que deve ser copiado com segurança e com que frequência, para recuperação após falha de hardware, erro humano ou incidente de segurança.

Para comandos Artisan, testes e o que **não** fazer no banco local, ver [desenvolvimento-banco-dados.md](./desenvolvimento-banco-dados.md).

## O que incluir

1. **Base de dados**  
   Exportação completa (MySQL/MariaDB: `mysqldump`, ou cópia do ficheiro SQLite em desenvolvimento). Contém utilizadores, empresas, papéis, processos, clientes, **registos da tabela `equipe_logs`**, tokens de redefinição de senha (`password_reset_tokens`), etc.

2. **Ficheiros enviados**  
   Tudo o que estiver em `storage/app` (anexos de processos, clientes, embarcações, se aplicável). Confirmar o disco configurado em `FILESYSTEM_DISK`.

3. **Configuração (sem segredos em repositório)**  
   Guardar de forma segura uma referência dos valores de produção: `APP_KEY`, credenciais de base de dados, `MAIL_*`, URLs. O ficheiro `.env` não deve ir para o Git; pode constar de um cofre (gestor de passwords) ou backup encriptado separado.

## Frequência sugerida

| Ambiente | Base de dados | Ficheiros em `storage` |
|----------|---------------|-------------------------|
| Produção | Diário (mínimo); horário se o volume for alto | Diário ou em sincronização com a BD |
| Homologação | Antes de alterações grandes | Quando houver dados de teste relevantes |

## Retenção

- Manter pelo menos **7 cópias diárias** e **4 semanais** em produção (ajustar conforme compliance interno).
- Testar **restauro** pelo menos trimestralmente (restaurar num ambiente isolado e validar login e um fluxo crítico).

## Onde armazenar

- Preferir destino **fora do servidor** da aplicação (object storage, outro datacenter, ou serviço de backup do fornecedor).
- Cofre encriptado para ficheiros que contenham dados pessoais.

## Após incidente

1. Revogar sessões se necessário (por exemplo, limpar a tabela `sessions` se `SESSION_DRIVER=database`, ou reiniciar o driver Redis/Memcached).
2. Rotacionar `APP_KEY` só se houver suspeita de compromisso da chave (implica invalidar cookies/sessões encriptadas).
3. Auditar `equipe_logs` para ações suspeitas após restauro.

## Correio e convites

Os convites e links de redefinição de senha dependem de **SMTP** (ou outro mailer) configurado. Em ambiente sem correio, os utilizadores podem ser criados com senha manual ou reenviar o link depois de configurar `MAIL_*` (ver `.env.example`).
