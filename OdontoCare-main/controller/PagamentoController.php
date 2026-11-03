<?php

session_start();

include_once "../permissao.php";

if(!isset($_SESSION["usuario"])){
    header("location:../login.php");
    exit;
}

if(!podeEditar()){
    header("location:../index.php");
    exit;
}

validarPostCsrf();

include_once "../model/pagamento.php";
include_once "../dao/PagamentoDao.php";
include_once "../config/validacaoFinanceira.php";

if (($_POST["acao"] ?? null) === "excluir")
{
    $pagamentoDao = new PagamentoDao();

    $pagamentoDao->delete(idExclusao());

    header("location:../pagamento.php");
    exit;
}

if(isset($_POST["btGravar"]))
{
    $id = financeiroId('txtIdPagamento', true);
    $valor = financeiroValor('txtValorTotal');
    $forma = financeiroOpcao('txtFormaPagamento', ['Pix', 'Dinheiro', 'Cartão de Débito', 'Cartão de Crédito']);
    $status = financeiroOpcao('txtStatusPagamento', ['Pendente', 'Pago', 'Cancelado']);
    $data = financeiroData('txtDataPagamento');
    $consulta = financeiroId('cbConsulta');
    financeiroRegistro('consulta', 'idConsulta', $consulta);
    if ($id !== null) financeiroRegistro('pagamento', 'idPagamento', $id);
    $pagamento = new Pagamento(

        $id,
        $valor,
        $forma,
        $status,
        $data,
        $consulta

    );

    $pagamentoDao = new PagamentoDao();

    if(empty($_POST["txtIdPagamento"])){

        $pagamentoDao->create($pagamento);

    } else {

        $pagamentoDao->update($pagamento);

    }

    header("location:../pagamento.php");
    exit;
}
