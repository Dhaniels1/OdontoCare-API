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

include_once "../config/conexao.php";
include_once "../model/pagamento.php";
include_once "../dao/PagamentoDao.php";

if(isset($_GET["id"]))
{
    $pagamentoDao = new PagamentoDao();

    $pagamentoDao->delete($_GET["id"]);

    header("location:../pagamento.php");
    exit;
}

if(isset($_POST["btGravar"]))
{
    $pagamento = new Pagamento(

        $_POST["txtIdPagamento"],
        $_POST["txtValorTotal"],
        $_POST["txtFormaPagamento"],
        $_POST["txtStatusPagamento"],
        $_POST["txtDataPagamento"],
        $_POST["cbConsulta"]

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