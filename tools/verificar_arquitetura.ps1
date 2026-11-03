$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot
$violations = @()
foreach ($layer in @('OdontoCare.Domain', 'OdontoCare.Application')) {
    $files = Get-ChildItem (Join-Path $repo $layer) -Recurse -File | Where-Object { $_.Extension -in @('.cs', '.csproj') -and $_.FullName -notmatch '[\\/](obj|bin)[\\/]' }
    $violations += $files | Select-String -Pattern 'Microsoft.EntityFrameworkCore|OdontoCare.Infrastructure|MySql|DbContext'
}
$violations += Get-ChildItem (Join-Path $repo 'OdontoCare-main') -Filter *.php -Recurse | Select-String -Pattern 'new\s+PDO|->query\(|->prepare\(|conexao::conectar'
if ($violations.Count -gt 0) { $violations | ForEach-Object { Write-Output "$($_.Path):$($_.LineNumber)" }; throw 'Dependência proibida ou acesso direto ao banco encontrado.' }
Write-Output 'Arquitetura: Domain/Application sem EF/Infrastructure; cliente PHP sem acesso direto ao banco.'
