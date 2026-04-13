Para regenerar o ANEXO 2-G após mudanças no modelo oficial:

1. Copie o arquivo legado (ex.: 2G.php) para este diretório como "2G.php" — sem credenciais de BD no arquivo, se possível.
2. Execute: php scripts/extract_2g_svg.php
3. Opcional: php artisan db:seed --class=DocumentoModelosSeeder

Se não existir 2G.php aqui, o script usa c:/Users/Win/Downloads/2G.php (apenas nesta máquina).
