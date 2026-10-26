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
include_once "../model/orcamento.php";
include_once "../dao/OrcamentoDao.php";

if(isset($_GET["id"]))
{
    $orcamentoDao = new OrcamentoDao();

    $orcamentoDao->delete($_GET["id"]);

    header("location:../orcamento.php");
    exit;
}

if(isset($_POST["btGravar"]))
{
    $orcamento = new Orcamento(
        $_POST["txtIdOrcamento"],
        $_POST["txtValorTotal"],
        $_POST["txtDataOrcamento"],
        $_POST["txtStatusOrcamento"],
        $_POST["txtObservacoes"],
        $_POST["cbPaciente"]
    );

    $orcamentoDao = new OrcamentoDao();

    if(empty($_POST["txtIdOrcamento"])){

        $orcamentoDao->create($orcamento);

    } else {

        $orcamentoDao->update($orcamento);

    }

    header("location:../orcamento.php");
    exit;
}