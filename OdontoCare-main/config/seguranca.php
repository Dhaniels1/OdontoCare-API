<?php

function e($valor)
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonScript($valor)
{
    return json_encode($valor, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
}

function csrfToken()
{
    if (session_status() !== PHP_SESSION_ACTIVE) throw new LogicException('Sessão não iniciada.');
    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']))
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrfCampo()
{
    echo '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function validarPostCsrf()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('Utilize o formulário para realizar esta operação.');
    }
    $token = $_POST['csrf'] ?? null;
    if (!is_string($token) || !isset($_SESSION['csrf']) ||
        !is_string($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('A sessão do formulário é inválida. Recarregue a página e tente novamente.');
    }
}

function idExclusao()
{
    $id = $_POST['id'] ?? null;
    if (!is_string($id) || !ctype_digit($id) || (int)$id < 1 || (int)$id > 2147483647) {
        http_response_code(400);
        exit('Identificador inválido.');
    }
    return (int)$id;
}
