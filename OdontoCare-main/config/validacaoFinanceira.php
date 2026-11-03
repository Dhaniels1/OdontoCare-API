<?php

function financeiroFalha($status, $mensagem)
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    exit($mensagem);
}

function financeiroTexto($campo, $maximo, $obrigatorio = false)
{
    $valor = $_POST[$campo] ?? '';
    if (!is_string($valor)) financeiroFalha(400, 'Campo inválido.');
    $valor = trim($valor);
    if (($obrigatorio && $valor === '') || mb_strlen($valor, 'UTF-8') > $maximo)
        financeiroFalha(400, 'Campo obrigatório ausente ou maior que o permitido.');
    return $valor;
}

function financeiroValor($campo)
{
    $valor = financeiroTexto($campo, 11, true);
    // DECIMAL(10,2): oito dígitos inteiros, sem arredondar frações de centavo.
    if (!preg_match('/\A[0-9]{1,8}(?:\.[0-9]{1,2})?\z/', $valor))
        financeiroFalha(400, 'Informe um valor não negativo com no máximo duas casas decimais.');
    return $valor;
}

function financeiroData($campo, $obrigatoria = true)
{
    $valor = financeiroTexto($campo, 10, $obrigatoria);
    if ($valor === '' && !$obrigatoria) return null;
    if (!preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $valor, $partes) ||
        (int)$partes[1] < 1000 || !checkdate((int)$partes[2], (int)$partes[3], (int)$partes[1]))
        financeiroFalha(400, 'Informe uma data válida.');
    return $valor;
}

function financeiroOpcao($campo, $opcoes)
{
    $valor = financeiroTexto($campo, 30, true);
    if (!in_array($valor, $opcoes, true)) financeiroFalha(400, 'Opção inválida.');
    return $valor;
}

function financeiroId($campo, $opcional = false)
{
    $valor = financeiroTexto($campo, 10, !$opcional);
    if ($valor === '' && $opcional) return null;
    if (!ctype_digit($valor) || (int)$valor < 1 || (int)$valor > 2147483647)
        financeiroFalha(400, 'Identificador inválido.');
    return (int)$valor;
}

function financeiroRegistro($tabela, $coluna, $id)
{
    require_once __DIR__ . '/../api/DadosApi.php';
    $rotas = ['paciente'=>'pacientes', 'consulta'=>'consultas', 'orcamento'=>'orcamentos', 'pagamento'=>'pagamentos', 'procedimento'=>'procedimentos'];
    if (!isset($rotas[$tabela])) financeiroFalha(400, 'Registro inválido.');
    if (dadosApi('GET', 'api/'.$rotas[$tabela].'/'.(int)$id, null, true) === false)
        financeiroFalha(400, 'Registro informado não existe.');
}

function financeiroErroBanco()
{
    error_log('OdontoCare: falha no acesso ao módulo financeiro.');
    financeiroFalha(500, 'Não foi possível concluir a operação. Tente novamente.');
}
