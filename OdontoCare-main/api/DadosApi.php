<?php
require_once __DIR__ . '/ApiClient.php';
// Mantém o status da API; nunca transforma falha em lista vazia ou gravação bem-sucedida.
function dadosApi($metodo, $rota, $corpo = null, $ausentePermitido = false)
{
    $r = ApiClient::requisitar($metodo, $rota, $corpo);
    if ($r['sucesso']) return $r['dados'];
    if ($ausentePermitido && $r['status'] === 404) return false;
    exibirErroApiPagina($r['mensagem'] ?: 'Não foi possível concluir a operação.', $r['status'] ?: 503);
}
/** Página de erro segura e legível quando a API falhar fora de um formulário. */
function exibirErroApiPagina($mensagem, $status)
{
    require_once __DIR__ . '/../config/seguranca.php';
    $status = (int)$status;
    if ($status < 400 || $status > 599) $status = 503;
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    $titulo = $status === 403 ? 'Acesso não permitido' : 'Não foi possível concluir a operação';
    echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . e($titulo) . '</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '</head><body class="bg-light"><main class="container mt-5">'
        . '<div class="alert alert-danger" role="alert"><h1 class="h4">' . e($titulo) . '</h1>'
        . '<p>' . e($mensagem) . '</p><p class="small">HTTP ' . $status . '</p>'
        . '<a class="btn btn-primary" href="/index.php">Voltar ao sistema</a></div>'
        . '</main></body></html>';
    exit;
}
function dataFormularioApi($dados)
{
    if (!is_array($dados)) return $dados;
    foreach (['dataPagamento', 'dataOrcamento'] as $campo)
        if (!empty($dados[$campo])) $dados[$campo] = substr($dados[$campo], 0, 10);
    return $dados;
}
