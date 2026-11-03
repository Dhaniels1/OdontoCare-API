<?php
require_once __DIR__ . '/ApiClient.php';
// Mantém o status da API; nunca transforma falha em lista vazia ou gravação bem-sucedida.
function dadosApi($metodo, $rota, $corpo = null, $ausentePermitido = false)
{
    $r = ApiClient::requisitar($metodo, $rota, $corpo);
    if ($r['sucesso']) return $r['dados'];
    if ($ausentePermitido && $r['status'] === 404) return false;
    http_response_code($r['status'] ?: 503);
    header('Content-Type: text/plain; charset=utf-8');
    exit($r['mensagem'] ?: 'Não foi possível concluir a operação.');
}
function dataFormularioApi($dados)
{
    if (!is_array($dados)) return $dados;
    foreach (['dataPagamento', 'dataOrcamento'] as $campo)
        if (!empty($dados[$campo])) $dados[$campo] = substr($dados[$campo], 0, 10);
    return $dados;
}
