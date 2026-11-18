$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot
$violations = @()
foreach ($layer in @('OdontoCare.Domain', 'OdontoCare.Application')) {
    $files = Get-ChildItem (Join-Path $repo $layer) -Recurse -File | Where-Object { $_.Extension -in @('.cs', '.csproj') -and $_.FullName -notmatch '[\\/](obj|bin)[\\/]' }
    $violations += $files | Select-String -Pattern 'Microsoft.EntityFrameworkCore|OdontoCare.Infrastructure|MySql|DbContext'
}
$violations += Get-ChildItem (Join-Path $repo 'OdontoCare.API' 'Controllers') -Filter *.cs | Select-String -Pattern 'OdontoCare\.Domain'
$violations += Get-ChildItem (Join-Path $repo 'OdontoCare-main') -Filter *.php -Recurse | Select-String -Pattern 'new\s+PDO|->query\(|->prepare\(|conexao::conectar'
# Cada arquivo da Application declara somente um tipo publico.
$appFiles = Get-ChildItem (Join-Path $repo 'OdontoCare.Application') -Recurse -File -Filter *.cs | Where-Object { $_.FullName -notmatch '[\/](obj|bin)[\/]' }
foreach ($file in $appFiles) {
    $types = [regex]::Matches((Get-Content $file.FullName -Raw), '(?m)^\s*public\s+(?:(?:sealed|static|abstract|partial|readonly)\s+)*(?:class|interface|record(?:\s+struct)?|enum|struct)\s+[A-Za-z_]\w*')
    if ($types.Count -ne 1) { throw "Regra um tipo publico por arquivo violada: $($file.FullName) ($($types.Count) tipos)" }
}
if (Test-Path (Join-Path $repo 'OdontoCare.API/Security/JwtTokenService.cs')) { throw 'JwtTokenService ainda na camada API.' }
if ($violations.Count -gt 0) { $violations | ForEach-Object { Write-Output "$($_.Path):$($_.LineNumber)" }; throw 'Dependência proibida ou acesso direto ao banco encontrado.' }
Write-Output 'Arquitetura: Domain/Application sem EF/Infrastructure; controllers sem entidades do Domain; cliente PHP sem acesso direto ao banco.'
