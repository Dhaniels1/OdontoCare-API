$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $repo '.env'
if (Test-Path -LiteralPath $envFile) { throw '.env já existe; preservado. Use as credenciais existentes.' }
$adminSecret = Read-Host 'Senha do primeiro administrador (mínimo 12 caracteres)' -AsSecureString
$adminPassword = [Net.NetworkCredential]::new('', $adminSecret).Password
if ($adminPassword.Length -lt 12 -or $adminPassword.Contains("`n") -or $adminPassword.Contains("`r") -or $adminPassword.Contains("'")) {
    throw 'Use pelo menos 12 caracteres, sem aspas simples ou quebras de linha.'
}
function New-LocalSecret {
    $bytes = New-Object byte[] 48
    $rng = [Security.Cryptography.RandomNumberGenerator]::Create()
    try { $rng.GetBytes($bytes) } finally { $rng.Dispose() }
    return [Convert]::ToBase64String($bytes)
}
$lines = @(
    "MYSQL_ROOT_PASSWORD=$(New-LocalSecret)",
    "MYSQL_PASSWORD=$(New-LocalSecret)",
    "JWT_KEY=$(New-LocalSecret)",
    'ADMIN_LOGIN=admin',
    "ADMIN_PASSWORD='$adminPassword'"
)
[IO.File]::WriteAllLines($envFile, $lines, [Text.UTF8Encoding]::new($false))
$adminPassword = $null
Write-Output 'Configuração local criada em .env (ignorada pelo Git). Login inicial: admin. Senha: a informada no prompt.'
