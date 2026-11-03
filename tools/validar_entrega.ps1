$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot
if ($env:ODONTOCARE_VALIDACAO_ISOLADA -ne '1') { throw 'Use somente um banco descartável e configure ODONTOCARE_VALIDACAO_ISOLADA=1.' }
foreach ($key in @('ODONTOCARE_API_URL','ODONTOCARE_WEB_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS')) {
    if ($null -eq [Environment]::GetEnvironmentVariable($key)) { throw "Configure $key." }
}
& (Join-Path $PSScriptRoot 'verificar_arquitetura.ps1')
$suites = @{
    'financeiro'='validar_financeiro.php'; 'seguranca'='validar_seguranca_web.php';
    'estendida'='validar_integracao_estendida.php'; 'concorrencia'='validar_concorrencia_agenda.php';
    'api'='validar_entrega_api.php'; 'aceite'='validar_aceite_daniel.php'
}
foreach ($name in @('financeiro','seguranca','estendida','concorrencia','api','aceite')) {
    $env:ODONTOCARE_VALIDACAO_RESULTADO = Join-Path $repo "validacao-entrega-$name.json"
    & php (Join-Path $PSScriptRoot $suites[$name])
    if ($LASTEXITCODE -ne 0) { throw "Suíte $name falhou. Consulte o resultado antes de continuar." }
}
if ($env:ODONTOCARE_CACHE_LOG) {
    $env:ODONTOCARE_VALIDACAO_RESULTADO = Join-Path $repo 'validacao-entrega-cache.json'
    & php (Join-Path $PSScriptRoot 'validar_cache.php')
    if ($LASTEXITCODE -ne 0) { throw 'Falha no cache.' }
}
if ($env:ODONTOCARE_API_EXPIRACAO_URL) {
    $originalUrl = $env:ODONTOCARE_API_URL
    try {
        $env:ODONTOCARE_API_URL = $env:ODONTOCARE_API_EXPIRACAO_URL
        $env:ODONTOCARE_VALIDACAO_RESULTADO = Join-Path $repo 'validacao-entrega-expiracao.json'
        & php (Join-Path $PSScriptRoot 'validar_expiracao_jwt.php')
        if ($LASTEXITCODE -ne 0) { throw 'Falha na expiração JWT.' }
    } finally { $env:ODONTOCARE_API_URL = $originalUrl }
}
Write-Output 'Suítes solicitadas concluídas. Cache, expiração e erro 500 exigem suas configurações específicas descritas no relatório.'
