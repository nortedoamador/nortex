-- NorteX — recriar a base MySQL do zero (apaga todos os dados).
--
-- Use quando:
--   - Erro 1146 (tabela não existe, ex.: users, sessions)
--   - Erro 1813 (tablespace da tabela migrations existe / InnoDB inconsistente)
--
-- Como executar:
--   1. phpMyAdmin → base qualquer → separador SQL → colar este ficheiro → Executar
--   OU na linha de comandos (ajuste a pasta do XAMPP):
--      "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/NorteX/scripts/recreate_nortex_database.sql"
--
-- Depois, na pasta do projeto:
--   php artisan migrate --force
--   php artisan db:seed --force
--
-- Utilizador demo (após seed): admin@nortex.local / password

DROP DATABASE IF EXISTS `nortex`;
CREATE DATABASE `nortex` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



CNH_ZBARIMG_PATH	Ler o QR da CNH (melhor cenário)
CNH_TESSERACT_PATH	OCR quando não há QR ou o QR não é legível
Imagick no PHP	Converter PDF em imagem (aqui o php -m mostra imagick carregado)
Ghostscript (CNH_GHOSTSCRIPT_PATH ou no PATH)	Necessário para o Imagick tratar PDFs