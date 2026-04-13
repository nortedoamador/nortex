#Requires -Version 5.1
<#
.SYNOPSIS
  Permite leitura/escrita de PDF no ImageMagick (policy.xml).
  Cria policy.xml.bak antes de alterar.
#>

$ErrorActionPreference = 'Stop'

function Find-ImageMagickPolicyFiles {
    $roots = @(
        ${env:ProgramFiles},
        ${env:ProgramFiles(x86)}
    ) | Where-Object { $_ -and (Test-Path $_) }

    $files = [System.Collections.Generic.List[string]]::new()
    foreach ($root in $roots) {
        Get-ChildItem -Path $root -Directory -Filter 'ImageMagick*' -ErrorAction SilentlyContinue | ForEach-Object {
            Get-ChildItem -Path $_.FullName -Recurse -Filter 'policy.xml' -ErrorAction SilentlyContinue | ForEach-Object {
                [void]$files.Add($_.FullName)
            }
        }
    }
    return $files | Select-Object -Unique
}

$policies = @(Find-ImageMagickPolicyFiles)
if ($policies.Count -eq 0) {
    Write-Error "Nenhum policy.xml encontrado em Program Files (ImageMagick*). Instale o ImageMagick ou ajuste o script."
}

$pattern = '<policy\s+domain="coder"\s+rights="none"\s+pattern="PDF"\s*/>'
$replacement = '<policy domain="coder" rights="read|write" pattern="PDF" />'

foreach ($path in $policies) {
    $raw = Get-Content -LiteralPath $path -Raw -Encoding UTF8
    if ($raw -notmatch 'pattern="PDF"') {
        Write-Host "Ignorar (sem regra PDF): $path"
        continue
    }
    if ($raw -match 'pattern="PDF"' -and $raw -match 'rights="read\|write"') {
        Write-Host "Ja liberado: $path"
        continue
    }
    if ($raw -notmatch $pattern) {
        Write-Warning "Ficheiro com PDF policy em formato inesperado: $path - edite manualmente (ver docs/windows-imagick-ghostscript-pdf.md)."
        continue
    }

    $bak = "$path.bak.$(Get-Date -Format 'yyyyMMddHHmmss')"
    Copy-Item -LiteralPath $path -Destination $bak -Force
    Write-Host "Backup: $bak"

    $new = [regex]::Replace($raw, $pattern, $replacement, 1)
    Set-Content -LiteralPath $path -Value $new -Encoding UTF8 -NoNewline
    Write-Host "Atualizado: $path"
}

Write-Host "Concluido. Reinicie o Apache."
