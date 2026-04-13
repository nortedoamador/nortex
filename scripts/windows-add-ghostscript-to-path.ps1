#Requires -Version 5.1
<#
.SYNOPSIS
  Procura gswin64c.exe (Ghostscript) e adiciona a pasta bin ao PATH do utilizador se ainda nao estiver.
#>

$ErrorActionPreference = 'Stop'

$roots = @(
    ${env:ProgramFiles},
    ${env:ProgramFiles(x86)}
) | Where-Object { $_ -and (Test-Path $_) }

$found = @()
foreach ($root in $roots) {
    Get-ChildItem -Path (Join-Path $root 'gs') -Directory -ErrorAction SilentlyContinue | ForEach-Object {
        $exe = Get-ChildItem -Path $_.FullName -Recurse -Filter 'gswin64c.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($exe) {
            $found += $exe.DirectoryName
        }
    }
}

$found = $found | Select-Object -Unique
if ($found.Count -eq 0) {
    Write-Error "Ghostscript nao encontrado (gswin64c.exe). Instale a partir de https://ghostscript.com/releases/gsdnld.html e volte a executar este script."
}

$bin = $found[0]
Write-Host "Ghostscript bin: $bin"

$userPath = [Environment]::GetEnvironmentVariable('Path', 'User')
$parts = $userPath -split ';' | Where-Object { $_ -ne '' }
if ($parts -contains $bin) {
    Write-Host "Ja presente no PATH do utilizador."
    exit 0
}

$newPath = ($parts + $bin) -join ';'
[Environment]::SetEnvironmentVariable('Path', $newPath, 'User')
Write-Host "PATH do utilizador atualizado. Abra um novo terminal e execute: gswin64c.exe -version"
