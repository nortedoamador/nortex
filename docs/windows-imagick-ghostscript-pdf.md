# Windows: Ghostscript, Imagick (PHP 8.2 ZTS x64) e policy.xml (PDF)

Necessário para o PHP conseguir rasterizar PDF com `Imagick::readImage()` (quando alguma funcionalidade do projeto ler páginas de PDF via Imagick).

## 1. Ghostscript + PATH

1. Descarregar o instalador **AGPL** para Windows (64-bit) em  
   https://ghostscript.com/releases/gsdnld.html  
   (ex.: `gs10.xx.x_Win64.exe`).

2. Instalar (por defeito fica em `C:\Program Files\gs\gs10.xx\bin\`).

3. Adicionar ao **PATH** do sistema (ou do utilizador):
   - Painel de controlo → Sistema → Definições avançadas → Variáveis de ambiente  
   - Em **Path**, **Novo**: `C:\Program Files\gs\gs10.xx\bin` (ajuste à sua versão).

4. Confirmar num **novo** PowerShell ou CMD:
   ```bat
   gswin64c.exe -version
   ```

**Atalho:** no repositório, execute (PowerShell) `.\scripts\windows-add-ghostscript-to-path.ps1` após instalar o Ghostscript; o script procura `gswin64c.exe` e sugere/adiciona ao PATH do utilizador.

---

## 2. Imagick “correto” para este XAMPP

O PHP em uso é **8.2.x, Thread Safe (ZTS), x64** (`php -i` → `Thread Safety => enabled`).

1. **ImageMagick** (instalador Windows, 64-bit, **Q16 HDRI** ou Q16, versão estável):  
   https://imagemagick.org/script/download.php#windows  

   Anote a pasta de instalação (ex.: `C:\Program Files\ImageMagick-7.1.2-Q16-HDRI`).

2. **Extensão PHP `php_imagick.dll`** compatível com **PHP 8.2 + ZTS + x64**:
   - https://windows.php.net/downloads/pecl/releases/imagick/  
   - Escolha a pasta da versão estável mais recente e o ficheiro com **ts** e **x64** no nome (ex.: `php_imagick-3.7.0-8.2-ts-vs16-x64.zip`).

3. Extrair:
   - `php_imagick.dll` → `C:\xampp\php\ext\`
   - Outras DLL que vierem no zip (dependências) → `C:\xampp\php\` **ou** siga o `README` do pacote (alguns builds exigem DLLs do próprio ImageMagick no PATH).

4. Em `C:\xampp\php\php.ini`, adicionar ou descomentar:
   ```ini
   extension=imagick
   ```

5. Reiniciar Apache e validar:
   ```bat
   php -m
   ```
   Deve aparecer `imagick`.

6. Se `php -m` falhar por DLL em falta, coloque a pasta `bin` do **ImageMagick** no **PATH** do sistema (onde estão `CORE_RL_*.dll`).

### Aviso: `Unable to load dynamic library 'imagick'` / «Não foi possível encontrar o módulo especificado»

- Confirme que **`php_imagick.dll`** existe mesmo em `C:\xampp\php\ext\` (sem este ficheiro, o `extension=imagick` no `php.ini` não funciona).
- Se o `.dll` **está** na pasta `ext` e o erro continua, faltam **dependências** (outras `.dll` do zip PECL ou as `CORE_RL_*.dll` do ImageMagick): copie-as para **`C:\xampp\php\`** (junto ao `php.exe`) **ou** adicione o `bin` do ImageMagick ao **PATH** e reinicie o Apache.

### Verificação rápida

Use `php -m` (deve listar `imagick`) e, se aplicável, o snippet PHP no fim deste documento.

---

## 3. Liberar PDF no `policy.xml` (ImageMagick)

Por defeito o ImageMagick **bloqueia** o codificador PDF (`rights="none"`).

1. Localizar `policy.xml`:
   - ImageMagick 7: normalmente  
     `C:\Program Files\ImageMagick-7.x.x-Q16-HDRI\config\policy.xml`  
     ou  
     `...\etc\ImageMagick-7\policy.xml`  
   (use a pasta real da sua instalação.)

2. **Fechar** serviços que usem ImageMagick (Apache) antes de editar, ou editar como administrador.

3. Procurar uma linha como:
   ```xml
   <policy domain="coder" rights="none" pattern="PDF" />
   ```

4. Alterar para permitir leitura (e escrita, se precisar):
   ```xml
   <policy domain="coder" rights="read|write" pattern="PDF" />
   ```

   Ou comentar/remover essa entrada **PDF** (menos explícito; preferível a linha acima).

5. Guardar, reiniciar Apache e testar rasterização de PDF (ex.: snippet abaixo ou o fluxo da aplicação que use Imagick).

**Atalho:** execute como administrador (recomendado) ou utilizador com permissão de escrita na pasta do ImageMagick:

```powershell
cd c:\xampp\htdocs\NorteX
.\scripts\windows-patch-imagemagick-pdf-policy.ps1
```

O script cria `policy.xml.bak` antes de alterar.

---

## Verificação rápida (PHP)

```php
<?php
$im = new Imagick();
$im->setResolution(300, 300);
$im->readImage('C:\\caminho\\para\\teste.pdf[0]');
echo $im->getImageWidth();
```

Se aparecer erro de *policy* ou *not authorized*, volte ao passo 3. Se aparecer erro de Ghostscript, volte ao passo 1 (PATH).
