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
include_once "../model/procedimento.php";
include_once "../dao/ProcedimentoDao.php";

if(isset($_GET["id"]))
{
    $procedimentoDao = new ProcedimentoDao();

    $procedimentoDao->delete($_GET["id"]);

    header("location:../procedimento.php");
    exit;
}

if(isset($_POST["btGravar"]))
{
    $procedimento = new Procedimento(

        $_POST["txtIdProcedimento"],
        $_POST["txtNomeProcedimento"],
        $_POST["txtDescricao"],
        $_POST["txtValorProcedimento"]

    );

    $procedimentoDao = new ProcedimentoDao();

    if(empty($_POST["txtIdProcedimento"])){

        $procedimentoDao->create($procedimento);

    } else {

        $procedimentoDao->update($procedimento);

    }

    header("location:../procedimento.php");
    exit;
}